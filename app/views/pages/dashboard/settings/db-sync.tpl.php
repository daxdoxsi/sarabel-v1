<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>DB Sync</h1>
                <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                    <ol class="breadcrumb pt-0">
                        <li class="breadcrumb-item">
                            <a href="/dashboard">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="/dashboard/settings">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">DB-Sync</li>
                    </ol>
                </nav>
                <div class="separator mb-5"></div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3>Press one of the buttons below to start the DB synchronization with the Asana API</h3>
                        <button id="btn-db-sync-refresh" type="button" class="btn btn-outline-primary mb-1">Start Refresh Synchronization (Quick)</button>
                        <button id="btn-db-sync-full" type="button" class="btn btn-outline-primary mb-1">Start Full Synchronization (It take a while)</button>
                        <button id="btn-db-sync-reset" type="button" class="btn btn-outline-primary mb-1">Reset the Synchronization (Stops any active process)</button>
                    </div>
                </div>
                <div class="card mb-4">
                    <div id="container-db-sync-log" class="card-body">
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
