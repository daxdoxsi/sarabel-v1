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
                        <a href="/">
                            <span class="logo-single"></span>
                        </a>
                        <?php
                        if (status_flash()):
                        ?>
                        <h6 class="mb-4"><?php echo get_flash(); ?></h6>
                        <?php
                        endif;
                        ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/asana/authorize" class="btn btn-primary btn-lg btn-shadow">CONNECT WITH ASANA</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
