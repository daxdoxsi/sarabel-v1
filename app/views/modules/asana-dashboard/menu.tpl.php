<div class="menu">
    <div class="main-menu">
        <div class="scroll">
            <ul class="list-unstyled">
                <li<?php echo ( request_uri() == 'dashboard' || strstr(request_uri(), 'dashboard/graph') !== false ? ' class="active"' : '' );?>>
                    <a href="#dashboard">
                        <i class="iconsminds-shop-4"></i>
                        <span>Dashboards</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/reports') !== false ? ' class="active"' : '' );?>>
                    <a href="#reports">
                        <i class="iconsminds-digital-drawing"></i> Reports
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search') !== false ? ' class="active"' : '' );?>>
                    <a href="#search">
                        <i class="iconsminds-air-balloon-1"></i> Search
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators') !== false ? ' class="active"' : '' );?>>
                    <a href="#collaborators">
                        <i class="iconsminds-pantone"></i> Collaborators
                    </a>
                </li>
                <li<?php echo ( strstr( request_uri(), 'dashboard/real-time') !== false ? ' class="active"' : '' );?>>
                    <a href="#real-time">
                        <i class="iconsminds-three-arrow-fork"></i> Real-Time
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sub-menu">
        <div class="scroll">
            <ul class="list-unstyled" data-link="dashboard">
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseAuthorization" aria-expanded="true"
                       aria-controls="collapseAuthorization" class="rotate-arrow-icon opacity-50">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Projects</span>
                    </a>
                    <div id="collapseAuthorization" class="collapse show">
                        <ul class="list-unstyled inner-level-menu">
                            <?php if ( is_array($tpl_projects) && count($tpl_projects) > 0 ): ?>
                            <li<?php echo (request_uri() == 'dashboard' ? ' class="active"' : '' );?>>
                                <a href="/dashboard">
                                    <i class="simple-icon-pie-chart"></i> <span
                                            class="d-inline-block">Global</span>
                                </a>
                            </li>
                            <?php foreach ($tpl_projects as $project): ?>
                            <li<?php echo (request_uri() == substr($project['url'], 1) ? ' class="active"' : '' );?>>
                                <a href="<?php echo $project['url'];?>">
                                    <i class="<?php echo $project['icon'];?>"></i> <span
                                            class="d-inline-block"><?php echo $project['name'];?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled" data-link="reports" id="reports">
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseAuthorization" aria-expanded="true"
                       aria-controls="collapseAuthorization" class="rotate-arrow-icon opacity-50">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Email team</span>
                    </a>
                    <div id="collapseAuthorization" class="collapse show">
                        <ul class="list-unstyled inner-level-menu">
                            <li<?php echo ( strstr(request_uri(), 'dashboard/reports/email/capacity-analysis') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/reports/email/capacity-analysis">
                                    <i class="simple-icon-user-following"></i> <span
                                            class="d-inline-block">Capacity Analysis</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/reports/email/summary') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/reports/email/summary">
                                    <i class="simple-icon-user-following"></i> <span
                                            class="d-inline-block">Summary</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseAuthorization" aria-expanded="true"
                       aria-controls="collapseAuthorization" class="rotate-arrow-icon opacity-50">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Dev team</span>
                    </a>
                    <div id="collapseAuthorization" class="collapse show">
                        <ul class="list-unstyled inner-level-menu">
                            <li<?php echo ( strstr(request_uri(), 'dashboard/reports/dev/capacity-analysis') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/reports/dev/capacity-analysis">
                                    <i class="simple-icon-user-following"></i> <span
                                            class="d-inline-block">Capacity Analysis</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/reports/dev/summary') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/reports/dev/summary">
                                    <i class="simple-icon-user-following"></i> <span
                                            class="d-inline-block">Summary</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseAuthorization" aria-expanded="true"
                       aria-controls="collapseAuthorization" class="rotate-arrow-icon opacity-50">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Custom Projects</span>
                    </a>
                    <div id="collapseAuthorization" class="collapse show">
                        <ul class="list-unstyled inner-level-menu">
                            <li<?php echo ( strstr(request_uri(), 'dashboard/reports/custom/analysis') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/reports/custom/analysis">
                                    <i class="simple-icon-user-following"></i> <span
                                            class="d-inline-block">Analysis</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>

            <ul class="list-unstyled" data-link="search">
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/team') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/team">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Global Team</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/projects') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/projects">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Projects</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/sections') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/sections">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Sections</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/tasks') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/tasks">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Tasks</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/stories') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/stories">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Stories</span>
                    </a>
                </li>
                <li<?php echo ( strstr(request_uri(), 'dashboard/search/advanced') !== false ? ' class="active"' : '' );?>>
                    <a href="/dashboard/search/advanced">
                        <i class="simple-icon-search"></i> <span class="d-inline-block">Advanced search</span>
                    </a>
                </li>
            </ul>

            <ul class="list-unstyled" data-link="collaborators">
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseForms" aria-expanded="true"
                       aria-controls="collapseForms" class="rotate-arrow-icon opacity-50">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Groups</span>
                    </a>
                    <div id="collapseForms" class="collapse show">
                        <ul class="list-unstyled inner-level-menu">
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/standard-devs') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/standard-devs">
                                    <i class="simple-icon-event"></i> <span class="d-inline-block">Standard Devs</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/custom-devs') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/custom-devs">
                                    <i class="simple-icon-doc"></i> <span class="d-inline-block">Custom Devs</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/email-devs') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/email-devs">
                                    <i class="simple-icon-check"></i> <span class="d-inline-block">Email Devs</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/qa-standard') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/qa-standard">
                                    <i class="simple-icon-check"></i> <span class="d-inline-block">QA Standard</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/qa-custom') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/qa-custom">
                                    <i class="simple-icon-check"></i> <span class="d-inline-block">QA Custom</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/collaborators/groups/project-manager') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/collaborators/groups/project-manager">
                                    <i class="simple-icon-magic-wand"></i> <span class="d-inline-block">Project Manager</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>

            <ul class="list-unstyled" data-link="real-time" id="menuTypes">
                <li>
                    <a href="#" data-toggle="collapse" data-target="#collapseMenuTypes" aria-expanded="true"
                       aria-controls="collapseMenuTypes" class="rotate-arrow-icon">
                        <i class="simple-icon-arrow-down"></i> <span class="d-inline-block">Transactions</span>
                    </a>
                    <div id="collapseMenuTypes" class="collapse show" data-parent="#menuTypes">
                        <ul class="list-unstyled inner-level-menu">
                            <li<?php echo ( strstr(request_uri(), 'dashboard/real-time/tasks') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/real-time/tasks">
                                    <i class="simple-icon-control-pause"></i> <span
                                            class="d-inline-block">Tasks</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/real-time/stories') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/real-time/stories">
                                    <i class="simple-icon-arrow-left mi-subhidden"></i> <span
                                            class="d-inline-block">Stories</span>
                                </a>
                            </li>
                            <li<?php echo ( strstr(request_uri(), 'dashboard/real-time/users') !== false ? ' class="active"' : '' );?>>
                                <a href="/dashboard/real-time/users">
                                    <i class="simple-icon-control-start mi-hidden"></i> <span
                                            class="d-inline-block">Users</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>

        </div>
    </div>
</div>

