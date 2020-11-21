$(function(){
    $('#ctl-workspace').change(function(){

        // Hide the workspace select
        $('#ctl-workspace').hide(500);

        // Writing the value in the label
        $('#txt-organization').html($( "#ctl-workspace option:selected" ).text());

        // Setting the value selected on workspace
        var workspace_value = $('#ctl-workspace option:selected').val();

        // Show the change organization option and bind actions
        $('.org-change').show(500).click(function(ev){
            ev.preventDefault();
            $('#ctl-workspace').show(500);
            // $('#ctl-team').hide(500);
            $('.container-team').hide(500)
            $(this).hide(500);
        });

        // Ajax request of teams list
        $.get( "/asana/parameters", { workspace: workspace_value }, function(data){
            for(var team in data) {
                $('#ctl-team').append( $('<option>', {value: team, text: data[team]} ) );
            }
            $('.container-team').show(500);
        });

    });
    $('#ctl-team').change(function(){

        // Hide the team select
        $('#ctl-team').hide(500);

        // Writing the value in the label
        $('#txt-team').html($( "#ctl-team option:selected" ).text());

        // Show the change organization option and bind actions
        $('.team-change').show(500).click(function(ev){
            ev.preventDefault();
            $('#ctl-team').show(500);
            $(this).hide(500);
            $('#btn-submit').attr('disabled','disabled');
        });

        // Activating submit button
        $('#btn-submit').removeAttr('disabled');

    });
});
