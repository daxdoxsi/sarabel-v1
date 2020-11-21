<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Search - <?php echo $content_title; ?></h1>
                <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                    <ol class="breadcrumb pt-0">
                        <li class="breadcrumb-item">
                            <a href="/dashboard">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="/dashboard/search">Search</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $content_title; ?></li>
                    </ol>
                </nav>
                <div class="separator mb-5"></div>
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</main>
