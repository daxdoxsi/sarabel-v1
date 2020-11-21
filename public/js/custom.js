$(function(){

    //////////////////////////////////////////////////////
    // These are the option for the parameter page
    //////////////////////////////////////////////////////

    if ( $('#ctl-workspace') ) {
        // Parameters selection via Ajax
        $('#ctl-workspace').change(function () {

            // Hide the workspace select
            $('#ctl-workspace').hide(500);

            // Writing the value in the label
            $('#txt-organization').html($("#ctl-workspace option:selected").text());

            // Setting the value selected on workspace
            var workspace_value = $('#ctl-workspace option:selected').val();

            // Show the change organization option and bind actions
            $('.org-change').show(500).click(function (ev) {
                ev.preventDefault();
                $('#ctl-workspace').show(500);
                // $('#ctl-team').hide(500);
                $('.container-team').hide(500)
                $(this).hide(500);
            });

            // Ajax request of teams list
            $.get("/asana/parameters", {workspace: workspace_value}, function (data) {
                for (var team in data) {
                    $('#ctl-team').append($('<option>', {value: team, text: data[team]}));
                }
                $('.container-team').show(500);
            });

        });
        $('#ctl-team').change(function () {

            // Hide the team select
            $('#ctl-team').hide(500);

            // Writing the value in the label
            $('#txt-team').html($("#ctl-team option:selected").text());

            // Show the change organization option and bind actions
            $('.team-change').show(500).click(function (ev) {
                ev.preventDefault();
                $('#ctl-team').show(500);
                $(this).hide(500);
                $('#btn-submit').attr('disabled', 'disabled');
            });

            // Activating submit button
            $('#btn-submit').removeAttr('disabled');

        });
    } // End Parameters Selectors

    //////////////////////////////////////////////////////
    // END: These are the option for the parameter page
    //////////////////////////////////////////////////////


    //////////////////////////////////////////////////////
    // Detecting if a DB_Sync is running in this moment
    //////////////////////////////////////////////////////

    var DB_Sync_detection = function () {

        // Verifying if is the DB_Sync page
        if ( $('#btn-db-sync-refresh').length == 0 || $('#btn-db-sync-full').length == 0 ) {
            return;
        }

        // Timer variable
        var db_sync_timer;
        var first_time = true;

        // Initial checking if the DB Sync process is running
        db_sync_timer = setInterval(function () {

            // Ajax request
            $.ajax({
                type: 'GET',
                url: '/dashboard/settings/db-sync/status',
                success:
                    function (data) {

                        if ( data.status === "COMPLETED" || data.message === "" ) {

                            clearInterval(db_sync_timer);

                            // Activating the DB_Sync button again
                            $('#btn-db-sync-refresh').removeAttr("disabled");
                            $('#btn-db-sync-full').removeAttr("disabled");

                            if (!first_time) {

                                // Show the log box final
                                $('#container-db-sync-log').html(data.message).show(500);

                            }

                            return true;

                        }
                        else {

                            // Disabling buttons
                            $('#btn-db-sync-refresh').attr("disabled", true);
                            $('#btn-db-sync-full').attr("disabled", true);

                            // Show the log box
                            $('#container-db-sync-log').html(data.message).show(500);

                        }

                        first_time = false;

                    },
                dataType: 'json',
                async: true
            });

        }, 2000);

    //////////////////////////////////////
    } // end function DB_Sync detection //
    //////////////////////////////////////

    //////////////////////////////////////
    /// Checking is BD_Sync is running  //
    //////////////////////////////////////

    DB_Sync_detection();

    // If REFRESH BUTTON exists then
    if ( $('#btn-db-sync-refresh').length !== 0 ) {

        // Assigning click event to the REFRESH BUTTON
        $('#btn-db-sync-refresh').click(function(ev) {

            // Avoid default behavior
            ev.preventDefault();

            // Disabled button to avoid process duplication
            $('#btn-db-sync-refresh').attr("disabled", true);
            $('#btn-db-sync-full').attr("disabled", true);

            // Reset the log container
            $('#container-db-sync-log').html('').hide(500);

            // Starting the sync process in background
            $.get('/dashboard/settings/db-sync/start');

            // Log monitory
            DB_Sync_detection();

        }); // end click

    } // end if


    // Button full synchronization
    if ( $('#btn-db-sync-full').length !== 0 ) {

        $('#btn-db-sync-full').click(function(ev) {

            // Avoid default behavior
            ev.preventDefault();

            // Disabled button to avoid process duplication
            $('#btn-db-sync-refresh').attr("disabled", true);
            $('#btn-db-sync-full').attr("disabled", true);

            // Reset the log container
            $('#container-db-sync-log').html('').hide(500);

            // Starting the full sync process on background
            $.get('/dashboard/settings/db-sync/start/full');

            // Log monitory
            DB_Sync_detection();

        }); // end click

    } // end if


    if ( $('#btn-db-sync-reset').length !== 0 ) {

        $('#btn-db-sync-reset').click(function(ev) {

            ev.preventDefault();

            $.get('/dashboard/settings/db-sync/reset', function (data) {

                if (data.status === 'RESET OK') {

                    // Activating the DB_Sync button again
                    $('#btn-db-sync-refresh').removeAttr("disabled");
                    $('#btn-db-sync-full').removeAttr("disabled");

                    // Stopping the timer
                    if (db_sync_timer) {
                        clearInterval(db_sync_timer);
                    }

                    alert('DB Sync process has been stopped')

                } else {

                    alert('Sorry, The process cannot be stopped');

                }

                // Log monitory
                //DB_Sync_detection();

            });

        });

    } // end if

});
