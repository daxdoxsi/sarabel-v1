<main>
    <div class="container">
        <div class="row h-100">
            <div class="col-12 col-md-10 mx-auto my-auto">
                <div class="card auth-card">
                    <div class="position-relative image-side ">

                        <p class=" text-white h2">MAGIC IS IN THE DETAILS</p>

                        <p class="white mb-0">
                            Please use your Asana&rsquo;s credentials to login.
                            <br>If you are not an Asana member, please
                            <a target="_blank" href="https://asana.com/create-account" class="white">register</a>.
                        </p>
                    </div>
                    <div class="form-side">

                        <p><img alt="Profile Picture" src="<?php echo $tpl_profile_picture;?>" />
                            <strong>Hey <?php echo $tpl_username; ?>,</strong><br>
                            You are authorized to use the Asana Reports Application, but before that please choose your
                            organization and team
                        </p>

                        <form method="post" action="/asana/parameters" id="frm-parameters">
                            <?php csrf(); ?>
                            <label>Organization: <span id="txt-organization"></span> <a class="org-change" href="#"><small>(change)</small></a></label>
                            <select name="workspace" id="ctl-workspace">
                                <option value="">Please choose your organization...</option>
                                <?php foreach ( $workspaces as $id => $workspace):?>
                                    <option value="<?php echo $id;?>">
                                        <?php echo $workspace;?>
                                    </option>
                                <?php endforeach;?>
                            </select>
                            <div class="container-team">
                                <label>Team: <span id="txt-team"></span> <a class="team-change" href="#"><small>(change)</small></a></label>
                                <select name="team" id="ctl-team">
                                    <option value="">Please choose your Team...</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <input disabled="disabled" id="btn-submit" type="submit" value="SAVE CONFIGURATION" class="btn btn-primary btn-lg btn-shadow">
                            </div>
                        </form>

                        <style>
                            .container-team,
                            .org-change,
                            .team-change {
                                display: none;
                            }
                            #btn-submit {
                                margin-top: 20px;
                            }
                            .form-side label {
                                display: block;
                                clear: both;
                                float: none;
                            }
                            .form-side p img {
                                float: left;
                                padding-right: 5px;
                            }
                        </style>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
