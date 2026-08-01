<div class="card shadow-sm">

    <div class="card-header">

        <h5>

            <i class="fas fa-heartbeat text-danger"></i>

            Business Health

        </h5>

    </div>

    <div class="card-body text-center">

        <h1>

            {{ $health['overall'] }}%

        </h1>

        <div class="progress">

            <div
                class="progress-bar bg-success"
                style="width: {{ $health['overall'] }}%">

            </div>

        </div>

        <br>

        <small>

            Excellent Business Health

        </small>

    </div>

</div>
