<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1>Webhooks</h1>

                <div class="top-right-button-container">
                    <div class="btn-group">
                        <button class="btn btn-outline-primary btn-lg dropdown-toggle" type="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            ACTIONS
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" id="item-new-webhook" href="#" data-toggle="modal"
                               data-backdrop="static" data-target="#addNewWebhookForm">Add new</a>
                            <a class="dropdown-item" id="item-delete-selected" href="#">Delete Selected</a>
                        </div>
                    </div>
                </div>

                <div class="modal fade modal-right" id="addNewWebhookForm" tabindex="-1" role="dialog"
                     aria-labelledby="addNewWebhookForm" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Webhook</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post" action="" class="needs-validation tooltip-label-right" novalidate>
                                <?php csrf() ?>
                                <div class="modal-body">
                                    <div class="form-group position-relative error-l-50">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="" required>
                                        <div class="invalid-tooltip">
                                            Name is required!
                                        </div>
                                    </div>

                                    <div class="form-group position-relative error-l-50">
                                        <label>Project</label>
                                        <select class="custom-select" name="project_gid" required>
                                            <option value="">Choose the project...&nbsp;</option>
                                            <?php foreach($projects as $project): ?>
                                            <option value="<?php echo $project['gid'];?>"><?php echo $project['name'];?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Project is required!
                                        </div>
                                    </div>

                                    <div class="form-group position-relative error-l-50">
                                        <label>Action</label>
                                        <select class="custom-select" name="action" required>
                                            <option value="">Choose the action</option>
                                            <option value="changed">Changed</option>
                                            <option value="added">Added</option>
                                            <option value="removed">Removed</option>
                                            <option value="deleted">Deleted</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Action is required!
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Fields</label>
                                        <textarea name="fields" placeholder="Comma separated" wrap="soft" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="form-group position-relative error-l-50">
                                        <label>Resource Type</label>
                                        <select name="resource_type" class="custom-select" required>
                                            <option value="">Choose the resource type</option>
                                            <option value="task">Task</option>
                                            <option value="story">Story</option>
                                            <option value="user">User</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Resource Type is required!
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-primary"
                                            data-dismiss="modal">Cancel</button>
                                    <input type="submit" class="btn btn-primary" value="Submit">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <nav class="breadcrumb-container d-none d-sm-block d-lg-inline-block" aria-label="breadcrumb">
                    <ol class="breadcrumb pt-0">
                        <li class="breadcrumb-item">
                            <a href="/dashboard">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="/dashboard/settings">Settings</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Webhooks</li>
                    </ol>
                </nav>

                <div class="mb-2">
                    <a class="btn pt-0 pl-0 d-inline-block d-md-none" data-toggle="collapse" href="#displayOptions"
                       role="button" aria-expanded="true" aria-controls="displayOptions">
                        Display Options
                        <i class="simple-icon-arrow-down align-middle"></i>
                    </a>
                    <div class="collapse dont-collapse-sm" id="displayOptions">
                        <div class="d-block d-md-inline-block">
                            <div class="search-sm d-inline-block float-md-left mr-1 mb-1 align-top">
                                <input class="form-control" placeholder="Search..." id="searchDatatable">
                            </div>
                        </div>
                        <div class="float-md-right dropdown-as-select" id="pageCountDatatable">
                            <span class="text-muted text-small">Displaying 1-10 of 40 items </span>
                            <button class="btn btn-outline-dark btn-xs dropdown-toggle" type="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                10
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#">5</a>
                                <a class="dropdown-item active" href="#">10</a>
                                <a class="dropdown-item" href="#">20</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator mb-5"></div>
                <form id="frm-delete" method="post" action="">
                    <?php csrf();?>
                    <input type="hidden" id="gids" name="gids" value="">
                </form>
                <div class="row">
                    <div class="col-12 mb-4 data-table-rows data-tables-hide-filter">
                        <table id="datatableRows" class="data-table responsive nowrap"
                               data-order="[[ 1, &quot;desc&quot; ]]">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Project</th>
                                <th>Created at</th>
                                <th>Resource Type</th>
                                <th>Filters</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th class="empty">&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($webhooks as $webhook): ?>
                            <tr>
                                <td>
                                    <p class="list-item-heading"><?php echo $webhook['name']; ?></p>
                                </td>
                                <td>
                                    <p class="text-muted"><?php echo $webhook['resource_name']; ?></p>
                                </td>
                                <td>
                                    <p class="text-muted"><?php echo $webhook['created_at']; ?></p>
                                </td>
                                <td>
                                    <p class="text-muted"><?php echo ucfirst(strtolower($webhook['resource_type'])); ?></p>
                                </td>
                                <td>
                                    <p class="text-muted"><?php echo print_r(json_decode($webhook['filters'])); ?></p>
                                </td>
                                <td>
                                    <p class="text-muted">Last failure: <?php echo $webhook['last_failure_at'] ?? 'None'; ?></p>
                                    <p class="text-muted">Details: <?php echo $webhook['last_failure_content'] ?? 'None'; ?></p>
                                    <p class="text-muted"><?php echo $webhook['last_success_at'] ?? 'None'; ?></p>
                                </td>
                                <td>
                                    <p class="text-muted"><?php echo ( $webhook['active'] == 1 ? 'Active' : 'Inactive'); ?></p>
                                </td>
                                <td>
                                    <label class="custom-control custom-checkbox mb-1 align-self-center data-table-rows-check">
                                        <input data-gid="<?php echo $webhook['gid']; ?>" type="checkbox" class="custom-control-input">
                                        <span class="custom-control-label">&nbsp;</span>
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>
</main>
