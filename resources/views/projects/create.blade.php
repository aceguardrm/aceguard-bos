@extends('layouts.aceguard')

@section('title', 'New Project')

@section('page-title', 'New Project')

@section(
    'page-subtitle',
    'Create a project, assign ownership and track delivery within an organisation workspace.'
)

@section('content')

<div class="ag-project-create">

    {{-- ================================================================
        HERO
    ================================================================ --}}

    <section class="ag-project-create__hero">

        <div>
            <span class="ag-project-create__kicker">
                ACEGUARD BOS · DELIVERY MANAGEMENT
            </span>

            <h2>Create New Project</h2>

            <p>
                Turn an organisation priority into accountable work with
                ownership, deadlines and measurable progress.
            </p>
        </div>

        <a
            href="{{ route('projects.index') }}"
            class="ag-project-create__back"
        >
            ← Back to Projects
        </a>

    </section>


    {{-- ================================================================
        VALIDATION ERRORS
    ================================================================ --}}

    @if ($errors->any())

        <div class="ag-project-create__errors">

            <strong>
                Please check the project details below.
            </strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- ================================================================
        PROJECT FORM
    ================================================================ --}}

    <form
        action="{{ route('projects.store') }}"
        method="POST"
        class="ag-project-form"
    >

        @csrf


        {{-- PROJECT DETAILS --}}

        <section class="ag-project-form__card">

            <div class="ag-project-form__heading">

                <div>
                    <span class="ag-project-form__eyebrow">
                        PROJECT DETAILS
                    </span>

                    <h3>
                        Project Information
                    </h3>

                    <p>
                        Define the work and connect it to an organisation
                        workspace.
                    </p>
                </div>

                <div class="ag-project-form__number">
                    01
                </div>

            </div>


            <div class="ag-project-form__grid">

                <div class="ag-project-form__field ag-project-form__field--full">

                    <label for="client_id">
                        Organisation Workspace
                        <span>*</span>
                    </label>

                    <select
                        name="client_id"
                        id="client_id"
                        required
                    >

                        <option value="">
                            Select organisation
                        </option>

                        @foreach($clients as $client)

                            <option
                                value="{{ $client->id }}"
                                @selected(
                                    old(
                                        'client_id',
                                        $selectedClient?->id
                                    ) == $client->id
                                )
                            >
                                {{ $client->company_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="ag-project-form__field ag-project-form__field--full">

                    <label for="name">
                        Project Name
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="e.g. Microsoft 365 Security Hardening"
                        required
                    >

                </div>


                <div class="ag-project-form__field ag-project-form__field--full">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="5"
                        placeholder="Describe the project objective, expected outcome and scope."
                    >{{ old('description') }}</textarea>

                </div>

            </div>

        </section>


        {{-- OWNERSHIP AND DELIVERY --}}

        <section class="ag-project-form__card">

            <div class="ag-project-form__heading">

                <div>
                    <span class="ag-project-form__eyebrow">
                        ACCOUNTABILITY
                    </span>

                    <h3>
                        Ownership & Delivery
                    </h3>

                    <p>
                        Assign responsibility and define the current delivery
                        position.
                    </p>
                </div>

                <div class="ag-project-form__number">
                    02
                </div>

            </div>


            <div class="ag-project-form__grid">

                <div class="ag-project-form__field">

                    <label for="owner_name">
                        Project Owner
                    </label>

                    <input
                        type="text"
                        name="owner_name"
                        id="owner_name"
                        value="{{ old('owner_name') }}"
                        placeholder="Owner name"
                    >

                </div>


                <div class="ag-project-form__field">

                    <label for="owner_email">
                        Owner Email
                    </label>

                    <input
                        type="email"
                        name="owner_email"
                        id="owner_email"
                        value="{{ old('owner_email') }}"
                        placeholder="owner@example.com"
                    >

                </div>


                <div class="ag-project-form__field">

                    <label for="status">
                        Status
                        <span>*</span>
                    </label>

                    <select
                        name="status"
                        id="status"
                        required
                    >

                        <option
                            value="planned"
                            @selected(old('status', 'planned') === 'planned')
                        >
                            Planned
                        </option>

                        <option
                            value="in_progress"
                            @selected(old('status') === 'in_progress')
                        >
                            In Progress
                        </option>

                        <option
                            value="on_hold"
                            @selected(old('status') === 'on_hold')
                        >
                            On Hold
                        </option>

                        <option
                            value="completed"
                            @selected(old('status') === 'completed')
                        >
                            Completed
                        </option>

                        <option
                            value="cancelled"
                            @selected(old('status') === 'cancelled')
                        >
                            Cancelled
                        </option>

                    </select>

                </div>


                <div class="ag-project-form__field">

                    <label for="priority">
                        Priority
                        <span>*</span>
                    </label>

                    <select
                        name="priority"
                        id="priority"
                        required
                    >

                        <option
                            value="low"
                            @selected(old('priority') === 'low')
                        >
                            Low
                        </option>

                        <option
                            value="medium"
                            @selected(old('priority', 'medium') === 'medium')
                        >
                            Medium
                        </option>

                        <option
                            value="high"
                            @selected(old('priority') === 'high')
                        >
                            High
                        </option>

                        <option
                            value="critical"
                            @selected(old('priority') === 'critical')
                        >
                            Critical
                        </option>

                    </select>

                </div>

            </div>

        </section>


        {{-- TIMELINE --}}

        <section class="ag-project-form__card">

            <div class="ag-project-form__heading">

                <div>
                    <span class="ag-project-form__eyebrow">
                        DELIVERY CONTROL
                    </span>

                    <h3>
                        Timeline & Progress
                    </h3>

                    <p>
                        Establish the delivery window and current completion
                        position.
                    </p>
                </div>

                <div class="ag-project-form__number">
                    03
                </div>

            </div>


            <div class="ag-project-form__grid">

                <div class="ag-project-form__field">

                    <label for="start_date">
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        value="{{ old('start_date') }}"
                    >

                </div>


                <div class="ag-project-form__field">

                    <label for="due_date">
                        Due Date
                    </label>

                    <input
                        type="date"
                        name="due_date"
                        id="due_date"
                        value="{{ old('due_date') }}"
                    >

                </div>


                <div class="ag-project-form__field ag-project-form__field--full">

                    <div class="ag-project-form__progress-label">

                        <label for="progress">
                            Progress
                        </label>

                        <strong id="progressValue">
                            {{ old('progress', 0) }}%
                        </strong>

                    </div>

                    <input
                        type="range"
                        name="progress"
                        id="progress"
                        min="0"
                        max="100"
                        step="5"
                        value="{{ old('progress', 0) }}"
                        oninput="
                            document.getElementById('progressValue')
                                .textContent = this.value + '%'
                        "
                    >

                    <div class="ag-project-form__range">

                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>

                    </div>

                </div>

            </div>

        </section>


        {{-- SOURCE AND NOTES --}}

        <section class="ag-project-form__card">

            <div class="ag-project-form__heading">

                <div>
                    <span class="ag-project-form__eyebrow">
                        GOVERNANCE
                    </span>

                    <h3>
                        Source & Notes
                    </h3>

                    <p>
                        Record where the project originated and any supporting
                        delivery information.
                    </p>
                </div>

                <div class="ag-project-form__number">
                    04
                </div>

            </div>


            <div class="ag-project-form__grid">

                <div class="ag-project-form__field">

                    <label for="source">
                        Project Source
                    </label>

                    <input
                        type="text"
                        name="source"
                        id="source"
                        value="{{ old('source') }}"
                        placeholder="e.g. Business Pulse"
                    >

                </div>


                <div class="ag-project-form__field">

                    <label for="source_reference">
                        Source Reference
                    </label>

                    <input
                        type="text"
                        name="source_reference"
                        id="source_reference"
                        value="{{ old('source_reference') }}"
                        placeholder="e.g. BP-001"
                    >

                </div>


                <div class="ag-project-form__field ag-project-form__field--full">

                    <label for="notes">
                        Internal Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        rows="5"
                        placeholder="Add internal delivery notes, dependencies or important context."
                    >{{ old('notes') }}</textarea>

                </div>

            </div>

        </section>


        {{-- ACTIONS --}}

        <div class="ag-project-form__actions">

            <a
                href="{{ route('projects.index') }}"
                class="ag-project-form__cancel"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="ag-project-form__submit"
            >
                Create Project
                <span>→</span>
            </button>

        </div>

    </form>

</div>


<style>

.ag-project-create {
    display: grid;
    gap: 24px;
}


/* HERO */

.ag-project-create__hero {
    background:
        linear-gradient(
            120deg,
            #101b36 0%,
            #14244a 55%,
            #2450ad 100%
        );

    border-radius: 26px;
    padding: 38px 40px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 30px;

    color: #ffffff;
}

.ag-project-create__kicker {
    display: block;
    margin-bottom: 13px;

    color: #76b7ff;

    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1.5px;
}

.ag-project-create__hero h2 {
    margin: 0 0 12px;
    font-size: 38px;
    font-weight: 500;
}

.ag-project-create__hero p {
    margin: 0;
    max-width: 760px;

    color: #d6e2f7;
    font-size: 17px;
    line-height: 1.7;
}

.ag-project-create__back {
    flex-shrink: 0;

    padding: 17px 24px;

    background: #ffffff;
    border-radius: 15px;

    color: #10182d;
    text-decoration: none;
    font-weight: 700;
}

.ag-project-create__back:hover {
    color: #10182d;
    text-decoration: none;
    transform: translateY(-1px);
}


/* ERRORS */

.ag-project-create__errors {
    padding: 20px 24px;

    background: #fff5f5;
    border: 1px solid #fecaca;
    border-radius: 16px;

    color: #991b1b;
}

.ag-project-create__errors ul {
    margin: 10px 0 0;
    padding-left: 20px;
}


/* FORM */

.ag-project-form {
    display: grid;
    gap: 22px;
}

.ag-project-form__card {
    padding: 30px;

    background: #ffffff;

    border:
        1px solid
        rgba(148, 163, 184, 0.25);

    border-radius: 22px;

    box-shadow:
        0 12px 35px
        rgba(15, 23, 42, 0.04);
}

.ag-project-form__heading {
    display: flex;
    justify-content: space-between;
    gap: 20px;

    margin-bottom: 28px;
    padding-bottom: 22px;

    border-bottom: 1px solid #e8edf5;
}

.ag-project-form__eyebrow {
    display: block;
    margin-bottom: 7px;

    color: #55708f;

    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.4px;
}

.ag-project-form__heading h3 {
    margin: 0 0 7px;

    color: #0f172a;

    font-size: 24px;
    font-weight: 600;
}

.ag-project-form__heading p {
    margin: 0;

    color: #718096;
    line-height: 1.6;
}

.ag-project-form__number {
    width: 52px;
    height: 52px;

    display: grid;
    place-items: center;

    flex-shrink: 0;

    background: #eef4ff;
    border-radius: 14px;

    color: #2563eb;
    font-weight: 700;
}


/* GRID */

.ag-project-form__grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 22px;
}

.ag-project-form__field {
    display: grid;
    gap: 9px;
}

.ag-project-form__field--full {
    grid-column: 1 / -1;
}


/* LABELS */

.ag-project-form__field label {
    margin: 0;

    color: #24324a;

    font-size: 14px;
    font-weight: 650;
}

.ag-project-form__field label span {
    color: #ef4444;
}


/* INPUTS */

.ag-project-form__field input[type="text"],
.ag-project-form__field input[type="email"],
.ag-project-form__field input[type="date"],
.ag-project-form__field select,
.ag-project-form__field textarea {

    width: 100%;

    padding: 14px 15px;

    background: #fbfcfe;

    border: 1px solid #dbe3ef;
    border-radius: 12px;

    color: #111827;

    font: inherit;

    outline: none;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.ag-project-form__field textarea {
    resize: vertical;
}

.ag-project-form__field input:focus,
.ag-project-form__field select:focus,
.ag-project-form__field textarea:focus {

    background: #ffffff;

    border-color: #2563eb;

    box-shadow:
        0 0 0 4px
        rgba(37, 99, 235, 0.09);
}


/* PROGRESS */

.ag-project-form__progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ag-project-form__progress-label strong {
    color: #2563eb;
    font-size: 18px;
}

.ag-project-form__field input[type="range"] {
    width: 100%;
    accent-color: #2563eb;
}

.ag-project-form__range {
    display: flex;
    justify-content: space-between;

    color: #94a3b8;
    font-size: 12px;
}


/* ACTIONS */

.ag-project-form__actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 14px;

    padding: 5px 0 30px;
}

.ag-project-form__cancel,
.ag-project-form__submit {

    min-height: 52px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;

    padding: 0 24px;

    border-radius: 13px;

    font-weight: 700;
    text-decoration: none;

    cursor: pointer;
}

.ag-project-form__cancel {
    background: #ffffff;
    border: 1px solid #dbe3ef;

    color: #475569;
}

.ag-project-form__submit {
    border: 0;

    background: #2563eb;

    color: #ffffff;
}

.ag-project-form__submit:hover {
    background: #1d4ed8;
}


/* RESPONSIVE */

@media (max-width: 800px) {

    .ag-project-create__hero {
        align-items: flex-start;
        flex-direction: column;

        padding: 28px;
    }

    .ag-project-create__hero h2 {
        font-size: 31px;
    }

    .ag-project-form__grid {
        grid-template-columns: 1fr;
    }

    .ag-project-form__field--full {
        grid-column: auto;
    }

    .ag-project-form__card {
        padding: 22px;
    }

    .ag-project-form__actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .ag-project-form__cancel,
    .ag-project-form__submit {
        width: 100%;
    }

}

</style>

@endsection