<style>
    .date-indicator {
        text-align: right;
    }
</style>
<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1><?php echo $content_title; ?></h1>
                <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                    <ol class="breadcrumb pt-0">
                        <li class="breadcrumb-item">
                            <a href="/dashboard">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $content_title; ?></li>
                    </ol>
                </nav>
                <div class="separator mb-5"></div>
            </div>
            <div class="col-lg-12 col-xl-12">
                <p class="date-indicator"><?php echo $tpl_date_start.' - '.$tpl_date_end; ?></p>
                <div class="icon-cards-row">
                    <div class="glide dashboard-numbers">
                        <div class="glide__track" data-glide-el="track">
                            <ul class="glide__slides">
                                <?php
                                $tpl_total = ( $tpl_total == 0 ? 1 : $tpl_total );
                                if (is_array($tpl_sections))
                                foreach($tpl_sections as $section_name => $section_total):
                                    if ( strstr('cs review', strtolower($section_name) ) !== false ) { $tpl_pc_cs_review = ($section_total * 100) / $tpl_total ;}
                                    if ( strstr('backlog', strtolower($section_name) ) !== false ) { $tpl_pc_backlog = ($section_total * 100) / $tpl_total ;}
                                    if ( strstr('done', strtolower($section_name) ) !== false ) { $tpl_pc_done = ($section_total * 100) / $tpl_total ;}
                                    if ( strstr('in progress', strtolower($section_name)) !== false ) { $tpl_pc_in_progress = ($section_total * 100) / $tpl_total ;}
                                ?>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-clock"></i>
                                            <p class="card-text mb-0"><?php echo $section_name;?></p>
                                            <p class="lead text-center"><?php echo $section_total;?></p>
                                        </div>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-clock"></i>
                                            <p class="card-text mb-0">Total tasks</p>
                                            <p class="lead text-center"><?php echo $tpl_total; ?></p>
                                        </div>
                                    </a>
                                </li>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-arrow-refresh"></i>
                                            <p class="card-text mb-0">Tasks completed</p>
                                            <p class="lead text-center"><?php echo $tpl_completed; ?></p>
                                        </div>
                                    </a>
                                </li>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-arrow-refresh"></i>
                                            <p class="card-text mb-0">Rush Approved</p>
                                            <p class="lead text-center"><?php echo $tpl_rush; ?></p>
                                        </div>
                                    </a>
                                </li>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-mail-read"></i>
                                            <p class="card-text mb-0">Task with Corrections</p>
                                            <p class="lead text-center"><?php echo $tpl_corrections; ?></p>
                                        </div>
                                    </a>
                                </li>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-mail-read"></i>
                                            <p class="card-text mb-0">QA Misses</p>
                                            <p class="lead text-center"><?php echo $tpl_qa_miss; ?></p>
                                        </div>
                                    </a>
                                </li>
                                <li class="glide__slide">
                                    <a href="#" class="card">
                                        <div class="card-body text-center">
                                            <i class="iconsminds-mail-read"></i>
                                            <p class="card-text mb-0">Task with updates</p>
                                            <p class="lead text-center"><?php echo $tpl_updates; ?></p>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Row sortable -->
                <div class="row sortable">
                    <div class="col-xl-3 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header p-0 position-relative">
                                <div class="position-absolute handle card-icon">
                                    <i class="simple-icon-shuffle"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Backlog</h6>
                                <div role="progressbar" class="progress-bar-circle position-relative" data-color="#922c88"
                                     data-trailColor="#d7d7d7" aria-valuemax="100" aria-valuenow="<?php echo $tpl_pc_backlog; ?>"
                                     data-show-percent="true">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header p-0 position-relative">
                                <div class="position-absolute handle card-icon">
                                    <i class="simple-icon-shuffle"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">CS Review</h6>
                                <div role="progressbar" class="progress-bar-circle position-relative" data-color="#922c88"
                                     data-trailColor="#d7d7d7" aria-valuemax="100" aria-valuenow="<?php echo $tpl_pc_cs_review;?>"
                                     data-show-percent="true">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header p-0 position-relative">
                                <div class="position-absolute handle card-icon">
                                    <i class="simple-icon-shuffle"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Task Done</h6>
                                <div role="progressbar" class="progress-bar-circle position-relative" data-color="#922c88"
                                     data-trailColor="#d7d7d7" aria-valuemax="100" aria-valuenow="<?php echo $tpl_pc_done; ?>"
                                     data-show-percent="true">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header p-0 position-relative">
                                <div class="position-absolute handle card-icon">
                                    <i class="simple-icon-shuffle"></i>
                                </div>
                            </div>
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Tasks Completed</h6>
                                <div role="progressbar" class="progress-bar-circle position-relative" data-color="#922c88"
                                     data-trailColor="#d7d7d7" aria-valuemax="100" aria-valuenow="<?php echo ($tpl_completed * 100) / $tpl_total;?>"
                                     data-show-percent="true">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row sortable -->

                <div class="row">

                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Tasks Assigned</h5>
                                <table class="data-table data-table-standard responsive nowrap"
                                       data-order="[[ 1, &quot;desc&quot; ]]">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Quantity</th>
                                        <th>Percent</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $tpl_assigned_total = ( $tpl_assigned_total == 0 ? 1 : $tpl_assigned_total );
                                    if (is_array($tpl_assigned))
                                    foreach($tpl_assigned as $person): ?>
                                    <tr>
                                        <td>
                                            <p class="list-item-heading"><?php echo ucwords(strtolower($person['name']));?></p>
                                        </td>
                                        <td>
                                            <p class="text-muted"><?php echo $person['qty'];?></p>
                                        </td>
                                        <td>
                                            <p class="text-muted"><?php echo round(($person['qty'] * 100) / $tpl_assigned_total,0); ?>%</p>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Task Type</h5>
                                <table class="data-table data-table-standard responsive nowrap"
                                       data-order="[[ 1, &quot;desc&quot; ]]">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Quantity</th>
                                        <th>Percent</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $tpl_type_total = ( $tpl_type_total == 0 ? 1 : $tpl_type_total );
                                    if (is_array($tpl_task_types))
                                    foreach($tpl_task_types as $task): ?>
                                        <tr>
                                            <td>
                                                <p class="list-item-heading"><?php echo ucwords(strtolower($task['name']));?></p>
                                            </td>
                                            <td>
                                                <p class="text-muted"><?php echo $task['qty'];?></p>
                                            </td>
                                            <td>
                                                <p class="text-muted"><?php echo round(($task['qty'] * 100) / $tpl_type_total,0); ?>%</p>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Developer Performance</h5>
                                <table class="data-table data-table-standard responsive nowrap"
                                       data-order="[[ 1, &quot;desc&quot; ]]">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Tasks</th>
                                        <th>Percent</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $tpl_developers_total = ( $tpl_developers_total == 0 ? 1 : $tpl_developers_total );
                                    if (is_array($tpl_developers))
                                    foreach($tpl_developers as $developer): ?>
                                    <tr>
                                        <td>
                                            <p class="list-item-heading"><?php echo ucwords(strtolower($developer['name']));?></p>
                                        </td>
                                        <td>
                                            <p class="text-muted"><?php echo $developer['qty'];?></p>
                                        </td>
                                        <td>
                                            <p class="text-muted"><?php echo round(($developer['qty'] * 100) / $tpl_developers_total,0); ?>%</p>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>





                </div> <!-- row -->


            </div> <!-- Col-lg-12 -->

        </div> <!-- row -->
    </div>
</main>
