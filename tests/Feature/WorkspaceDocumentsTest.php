<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WorkspaceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('workspace_documents');
    }

    private function workspace(string $name = 'Documents'): Client
    {
        return Client::create([
            'company_name' => $name,
            'contact_name' => 'Contact',
            'email' => strtolower($name).'@example.test',
            'status' => 'Active',
        ]);
    }

    private function member(Client $client, string $role): User
    {
        $user = User::factory()->create();
        $client->members()->attach($user->id, ['role' => $role]);
        return $user;
    }

    private function upload(Client $client): WorkspaceDocument
    {
        $this->post('/clients/'.$client->id.'/documents', [
            'title' => 'Private test document',
            'document' => UploadedFile::fake()->createWithContent(
                'notes.txt', 'Private workspace notes.'
            ),
        ])->assertRedirect(route('workspace-documents.index', $client));

        return WorkspaceDocument::latest('id')->firstOrFail();
    }

    public function test_editor_uploads_and_viewer_downloads_private_file(): void
    {
        $client = $this->workspace();
        $editor = $this->member($client, 'editor');
        $viewer = $this->member($client, 'viewer');

        $this->actingAs($editor);
        $document = $this->upload($client);

        $this->assertSame($client->id, $document->client_id);
        $this->assertSame($editor->id, $document->uploaded_by);
        $this->assertArrayNotHasKey('path', $document->toArray());
        Storage::disk('workspace_documents')->assertExists($document->path);

        $this->actingAs($viewer)
            ->get('/clients/'.$client->id.'/documents')
            ->assertOk()
            ->assertSee('Private test document')
            ->assertDontSee('Upload document');

        $response = $this->get(
            '/clients/'.$client->id.'/documents/'.$document->id.'/download'
        )->assertOk()
            ->assertDownload('notes.txt')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertStringContainsString(
            'no-store', $response->headers->get('Cache-Control')
        );
        $this->assertSame('Private workspace notes.', $response->streamedContent());
    }

    public function test_viewer_cannot_upload_and_outsider_cannot_access_documents(): void
    {
        $client = $this->workspace();
        $editor = $this->member($client, 'editor');
        $viewer = $this->member($client, 'viewer');

        $this->actingAs($editor);
        $document = $this->upload($client);

        $this->actingAs($viewer)->post('/clients/'.$client->id.'/documents', [
            'title' => 'Forbidden',
            'document' => UploadedFile::fake()->createWithContent('notes.txt', 'Text'),
        ])->assertForbidden();

        $this->assertDatabaseCount('workspace_documents', 1);

        $this->actingAs(User::factory()->create())
            ->get('/clients/'.$client->id.'/documents')->assertNotFound();

        $this->get(
            '/clients/'.$client->id.'/documents/'.$document->id.'/download'
        )->assertNotFound();
    }

    public function test_download_requires_the_correct_workspace_and_current_membership(): void
    {
        $first = $this->workspace('First');
        $second = $this->workspace('Second');
        $editor = $this->member($first, 'editor');
        $second->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor);
        $document = $this->upload($first);

        $this->get(
            '/clients/'.$second->id.'/documents/'.$document->id.'/download'
        )->assertNotFound();

        $this->get('/clients/'.$second->id.'/documents')
            ->assertOk()->assertDontSee('Private test document');

        $first->members()->detach($editor->id);

        $this->get(
            '/clients/'.$first->id.'/documents/'.$document->id.'/download'
        )->assertNotFound();
    }

    public function test_upload_uses_server_controlled_workspace_uploader_and_path(): void
    {
        $client = $this->workspace('First');
        $other = $this->workspace('Second');
        $editor = $this->member($client, 'editor');

        $this->actingAs($editor)->post('/clients/'.$client->id.'/documents', [
            'title' => 'Server controlled metadata',
            'document' => UploadedFile::fake()->createWithContent('notes.txt', 'Text'),
            'client_id' => $other->id,
            'uploaded_by' => 99999,
            'path' => '../../public/unsafe.php',
        ])->assertRedirect();

        $document = WorkspaceDocument::firstOrFail();
        $this->assertSame($client->id, $document->client_id);
        $this->assertSame($editor->id, $document->uploaded_by);
        $this->assertStringStartsWith($client->id.'/', $document->path);
        $this->assertStringNotContainsString('unsafe', $document->path);
    }

    public function test_disallowed_and_oversized_files_are_rejected(): void
    {
        $client = $this->workspace();
        $this->actingAs($this->member($client, 'editor'));

        $this->post('/clients/'.$client->id.'/documents', [
            'title' => 'Disallowed',
            'document' => UploadedFile::fake()->createWithContent(
                'script.php', '<?php echo "test";'
            ),
        ])->assertSessionHasErrors('document');

        $this->post('/clients/'.$client->id.'/documents', [
            'title' => 'Too large',
            'document' => UploadedFile::fake()->create(
                'large.txt', 10241, 'text/plain'
            ),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('workspace_documents', 0);
        $this->assertSame([], Storage::disk('workspace_documents')->allFiles());
    }


    public function test_editor_can_delete_document_and_file(): void
    {
        $client = $this->workspace();
        $this->actingAs($this->member($client, 'editor'));
        $document = $this->upload($client);
        $path = $document->path;

        $this->get('/clients/'.$client->id.'/documents')
            ->assertOk()
            ->assertSee('Delete')
            ->assertSee($document->size.' B');

        $this->delete('/clients/'.$client->id.'/documents/'.$document->id)
            ->assertRedirect(route('workspace-documents.index', $client));

        $this->assertDatabaseMissing('workspace_documents', ['id' => $document->id]);
        Storage::disk('workspace_documents')->assertMissing($path);

        $this->get(
            '/clients/'.$client->id.'/documents/'.$document->id.'/download'
        )->assertNotFound();
    }

    public function test_viewer_and_foreign_workspace_cannot_delete_document(): void
    {
        $client = $this->workspace('First');
        $other = $this->workspace('Second');
        $editor = $this->member($client, 'editor');
        $viewer = $this->member($client, 'viewer');
        $other->members()->attach($editor->id, ['role' => 'editor']);

        $this->actingAs($editor);
        $document = $this->upload($client);

        $this->actingAs($viewer)
            ->get('/clients/'.$client->id.'/documents')
            ->assertOk()
            ->assertDontSee('workspace-documents.destroy')
            ->assertDontSee('Permanently delete this document?');

        $this->delete('/clients/'.$client->id.'/documents/'.$document->id)
            ->assertForbidden();

        $this->actingAs($editor)
            ->delete('/clients/'.$other->id.'/documents/'.$document->id)
            ->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->delete('/clients/'.$client->id.'/documents/'.$document->id)
            ->assertNotFound();

        $this->assertDatabaseHas('workspace_documents', ['id' => $document->id]);
        Storage::disk('workspace_documents')->assertExists($document->path);
    }

    public function test_failed_file_deletion_preserves_database_record(): void
    {
        $client = $this->workspace();
        $this->actingAs($this->member($client, 'editor'));
        $document = $this->upload($client);
        $disk = Storage::disk('workspace_documents');

        Storage::shouldReceive('disk')
            ->with('workspace_documents')
            ->once()
            ->andReturn($diskMock = \Mockery::mock(
                \Illuminate\Filesystem\FilesystemAdapter::class
            ));

        $diskMock->shouldReceive('exists')
            ->with($document->path)->once()->andReturn(true);
        $diskMock->shouldReceive('delete')
            ->with($document->path)->once()->andReturn(false);

        $this->withoutExceptionHandling();

        try {
            $this->delete('/clients/'.$client->id.'/documents/'.$document->id);
            $this->fail('Expected storage deletion to fail.');
        } catch (\RuntimeException $error) {
            $this->assertSame(
                'Document file could not be deleted.',
                $error->getMessage()
            );
        }

        $this->assertDatabaseHas('workspace_documents', ['id' => $document->id]);
        $disk->assertExists($document->path);
    }

    public function test_missing_file_returns_not_found_and_guests_require_login(): void
    {
        $client = $this->workspace();

        $this->get('/clients/'.$client->id.'/documents')
            ->assertRedirect(route('login'));

        $this->actingAs($this->member($client, 'editor'));
        $document = $this->upload($client);
        Storage::disk('workspace_documents')->delete($document->path);

        $this->get(
            '/clients/'.$client->id.'/documents/'.$document->id.'/download'
        )->assertNotFound();
    }
}
