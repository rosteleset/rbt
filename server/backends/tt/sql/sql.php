<?php

    /**
     * backends tt namespace
     */

    namespace backends\tt {

        /**
         * internal.db tt class
         */

        class sql extends tt {

            /**
             * @inheritDoc
             */
            public function allow($params)
            {
                return true;
            }

            /**
             * @inheritDoc
             */
            public function capabilities()
            {
                return [
                    "mode" => "rw",
                ];
            }

            /**
             * @inheritDoc
             */
            public function cleanup()
            {
                return parent::cleanup(); // TODO: Change the autogenerated stub
            }

            /**
             * @inheritDoc
             */
            public function getProjects()
            {
                try {
                    $projects = $this->db->query("select project_id, acronym, project from tt_projects order by acronym", \PDO::FETCH_ASSOC)->fetchAll();
                    $_projects = [];

                    foreach ($projects as $project) {
                        $workflows = $this->db->query("select workflow from tt_projects_workflows where project_id = {$project["project_id"]}", \PDO::FETCH_ASSOC)->fetchAll();
                        $w = [];
                        foreach ($workflows as $workflow) {
                            $w[] = $workflow['workflow'];
                        }

                        $resolutions = $this->db->query("select issue_resolution_id from tt_projects_resolutions where project_id = {$project["project_id"]}", \PDO::FETCH_ASSOC)->fetchAll();
                        $r = [];
                        foreach ($resolutions as $resolution) {
                            $r[] = $resolution["issue_resolution_id"];
                        }

                        $customFields = $this->db->query("select issue_custom_field_id from tt_projects_custom_fields where project_id = {$project["project_id"]}", \PDO::FETCH_ASSOC)->fetchAll();
                        $f = [];
                        foreach ($customFields as $customField) {
                            $f[] = $customField["issue_custom_field_id"];
                        }

                        $users = $this->db->query("select project_role_id, uid, role_id from tt_projects_roles where project_id = {$project["project_id"]} and uid is not null");
                        $u = [];
                        foreach ($users as $user) {
                            $u[] = [
                                "projectRoleId" => $user["project_role_id"],
                                "uid" => $user["uid"],
                                "roleId" => $user["role_id"],
                            ];
                        }

                        $groups = $this->db->query("select project_role_id, gid, role_id from tt_projects_roles where project_id = {$project["project_id"]} and gid is not null");
                        $g = [];
                        foreach ($groups as $group) {
                            $g[] = [
                                "projectRoleId" => $group["project_role_id"],
                                "gid" => $group["gid"],
                                "roleId" => $group["role_id"],
                            ];
                        }

                        $_projects[] = [
                            "projectId" => $project["project_id"],
                            "acronym" => $project["acronym"],
                            "project" => $project["project"],
                            "workflows" => $w,
                            "resolutions" => $r,
                            "customFields" => $f,
                            "users" => $u,
                            "groups" => $g,
                        ];
                    }

                    return $_projects;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function addProject($acronym, $project)
            {
                $acronym = trim($acronym);
                $project = trim($project);

                if (!$acronym || !$project) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_projects (acronym, project) values (:acronym, :project)");
                    if (!$sth->execute([
                        ":acronym" => $acronym,
                        ":project" => $project,
                    ])) {
                        return false;
                    }

                    return $this->db->lastInsertId();
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function modifyProject($projectId, $acronym, $project)
            {
                if (!checkInt($projectId) || !trim($acronym) || !trim($project)) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("update tt_projects set acronym = :acronym, project = :project where project_id = $projectId");
                    $sth->execute([
                        ":acronym" => $acronym,
                        ":project" => $project,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function deleteProject($projectId)
            {
                if (!checkInt($projectId)) {
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_projects where project_id = $projectId");
                    // TODO: delete all derivatives
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function getWorkflowAliases()
            {
                try {
                    $workflows = $this->db->query("select workflow, alias from tt_workflows_aliases order by workflow", \PDO::FETCH_ASSOC)->fetchAll();
                    $_workflows = [];

                    foreach ($workflows as $workflow) {
                        $_workflows[] = [
                            "workflow" => $workflow["workflow"],
                            "alias" => $workflow["alias"],
                        ];
                    }

                    return $_workflows;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function setWorkflowAlias($workflow, $alias)
            {
                $alias = trim($alias);

                if (!$alias) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_workflows_aliases (workflow) values (:workflow)");
                    $sth->execute([
                        ":workflow" => $workflow,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                }

                try {
                    $sth = $this->db->prepare("update tt_workflows_aliases set alias = :alias where workflow = :workflow");
                    $sth->execute([
                        ":workflow" => $workflow,
                        ":alias" => $alias,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function setProjectWorkflows($projectId, $workflows)
            {
                // TODO: add transaction, commint, rollback

                if (!checkInt($projectId)) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_projects_workflows (project_id, workflow) values (:project_id, :workflow)");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_projects_workflows where project_id = $projectId");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    foreach ($workflows as $workflow) {
                        if (!$sth->execute([
                            ":project_id" => $projectId,
                            ":workflow" => $workflow,
                        ])) {
                            return false;
                        }
                        $w = $this->loadWorkflow($workflow);
                        if (!$w->initProject($projectId)) {
                            return false;
                        }
                    }
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function getStatuses()
            {
                try {
                    $statuses = $this->db->query("select issue_status_id, status, status_display from tt_issue_statuses order by status", \PDO::FETCH_ASSOC)->fetchAll();
                    $_statuses = [];

                    foreach ($statuses as $statuse) {
                        $_statuses[] = [
                            "statusId" => $statuse["issue_status_id"],
                            "status" => $statuse["status"],
                            "statusDisplay" => $statuse["status_display"],
                        ];
                    }

                    return $_statuses;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function moodifyStatus($statusId, $display)
            {
                $display = trim($display);

                if (!checkInt($statusId) || !$display) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("update tt_issue_statuses set status_display = :status_display where issue_status_id = $statusId");
                    $sth->execute([
                        ":status_display" => $display,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function getResolutions()
            {
                try {
                    $resolutions = $this->db->query("select issue_resolution_id, resolution from tt_issue_resolutions order by resolution", \PDO::FETCH_ASSOC)->fetchAll();
                    $_resolutions = [];

                    foreach ($resolutions as $resolution) {
                        $_resolutions[] = [
                            "resolutionId" => $resolution["issue_resolution_id"],
                            "resolution" => $resolution["resolution"],
                        ];
                    }

                    return $_resolutions;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function addResolution($resolution)
            {
                $resolution = trim($resolution);

                if (!$resolution) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_issue_resolutions (resolution) values (:resolution)");
                    if (!$sth->execute([
                        ":resolution" => $resolution,
                    ])) {
                        return false;
                    }

                    return $this->db->lastInsertId();
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function modifyResolution($resolutionId, $resolution)
            {
                $resolution = trim($resolution);

                if (!checkInt($resolutionId) || !$resolution) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("update tt_issue_resolutions set resolution = :resolution where issue_resolution_id = $resolutionId");
                    $sth->execute([
                        ":resolution" => $resolution,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function deleteResolution($resolutionId)
            {
                if (!checkInt($resolutionId)) {
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_issue_resolutions where issue_resolution_id = $resolutionId");
                    $this->db->exec("delete from tt_projects_resolutions where issue_resolution_id = $resolutionId");
                    // TODO: delete all derivatives
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function setProjectResolutions($projectId, $resolutions)
            {
                // TODO: add transaction, commint, rollback

                if (!checkInt($projectId)) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_projects_resolutions (project_id, issue_resolution_id) values (:project_id, :issue_resolution_id)");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_projects_resolutions where project_id = $projectId");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    foreach ($resolutions as $resolution) {
                        if (!checkInt($resolution)) {
                            return false;
                        }
                        if (!$sth->execute([
                            ":project_id" => $projectId,
                            ":issue_resolution_id" => $resolution,
                        ])) {
                            return false;
                        }
                    }
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function getCustomFields()
            {
                try {
                    $customFields = $this->db->query("select issue_custom_field_id, type, workflow, field, field_display, field_description, regex, link, format from tt_issue_custom_fields order by field", \PDO::FETCH_ASSOC)->fetchAll();
                    $_customFields = [];

                    foreach ($customFields as $customField) {
                        $options = $this->db->query("select issue_custom_field_option_id, option, option_display from tt_issue_custom_fields_options where issue_custom_field_id = {$customField["issue_custom_field_id"]} order by display_order", \PDO::FETCH_ASSOC)->fetchAll();
                        $_options = [];

                        foreach ($options as $option) {
                            $_options[] = [
                                "customFieldOptionId" => $option["issue_custom_field_option_id"],
                                "option" => $option["option"],
                                "optionDisplay" => $option["option_display"],
                            ];
                        }

                        $_customFields[] = [
                            "customFieldId" => $customField["issue_custom_field_id"],
                            "type" => $customField["type"],
                            "workflow" => $customField["workflow"],
                            "field" => $customField["field"],
                            "fieldDisplay" => $customField["field_display"],
                            "fieldDescription" => $customField["field_description"],
                            "regex" => $customField["regex"],
                            "link" => $customField["link"],
                            "format" => $customField["format"],
                            "options" => $_options,
                        ];
                    }

                    return $_customFields;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function addCustomField($type, $field, $fieldDisplay)
            {
                $type = trim($type);
                $field = trim($field);
                $fieldDisplay = trim($fieldDisplay);

                if (!$type || !$field || !$fieldDisplay) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("
                        insert into 
                            tt_issue_custom_fields (type, field, field_display, workflow)
                        values (:type, :field, :field_display, 0)");
                    if (!$sth->execute([
                        ":type" => $type,
                        ":field" => $field,
                        ":field_display" => $fieldDisplay,
                    ])) {
                        return false;
                    }

                    return $this->db->lastInsertId();
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function setProjectCustomFields($projectId, $customFields)
            {
                // TODO: add transaction, commint, rollback

                if (!checkInt($projectId)) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("insert into tt_projects_custom_fields (project_id, issue_custom_field_id) values (:project_id, :issue_custom_field_id)");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_projects_custom_fields where project_id = $projectId");
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                try {
                    foreach ($customFields as $customField) {
                        if (!checkInt($customField)) {
                            return false;
                        }
                        if (!$sth->execute([
                            ":project_id" => $projectId,
                            ":issue_custom_field_id" => $customField,
                        ])) {
                            return false;
                        }
                    }
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function addUserRole($projectId, $uid, $roleId)
            {
                if (!checkInt($projectId) || !checkInt($uid) || !checkInt($roleId)) {
                    return false;
                }

                try {
                    $this->db->exec("insert into tt_projects_roles (project_id, uid, role_id) values ($projectId, $uid, $roleId)");
                    return $this->db->lastInsertId();
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                }

                return false;
            }

            /**
             * @inheritDoc
             */
            public function addGroupRole($projectId, $gid, $roleId)
            {
                if (!checkInt($projectId) || !checkInt($gid) || !checkInt($roleId)) {
                    return false;
                }

                try {
                    $this->db->exec("insert into tt_projects_roles (project_id, gid, role_id) values ($projectId, $gid, $roleId)");
                    return $this->db->lastInsertId();
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                }

                return false;
            }

            /**
             * @inheritDoc
             */
            public function getRoles()
            {
                try {
                    $roles = $this->db->query("select role_id, name, name_display, level from tt_roles order by level", \PDO::FETCH_ASSOC)->fetchAll();
                    $_roles = [];

                    foreach ($roles as $role) {
                        $_roles[] = [
                            "roleId" => $role["role_id"],
                            "name" => $role["name"],
                            "nameDisplay" => $role["name_display"],
                            "level" => $role["level"],
                        ];
                    }

                    return $_roles;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function deleteRole($projectRoleId)
            {
                if (!checkInt($projectRoleId)) {
                    return false;
                }

                try {
                    $this->db->exec("delete from tt_projects_roles where project_role_id = $projectRoleId");

                    return true;
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }
            }

            /**
             * @inheritDoc
             */
            public function setRoleDisplay($roleId, $nameDisplay)
            {
                $nameDisplay = trim($nameDisplay);

                if (!checkInt($roleId) || !$nameDisplay) {
                    return false;
                }

                try {
                    $sth = $this->db->prepare("update tt_roles set name_display = :name_display where role_id = $roleId");
                    $sth->execute([
                        ":name_display" => $nameDisplay,
                    ]);
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function modifyCustomField($customFieldId, $fieldDisplay, $fieldDescription, $regex, $format, $link, $options)
            {
                if (!checkInt($customFieldId)) {
                    return false;
                }

                $cf = $this->db->query("select * from tt_issue_custom_fields where issue_custom_field_id = $customFieldId", \PDO::FETCH_ASSOC)->fetchAll();
                if (count($cf) !== 1) {
                    return false;
                }
                $cf = $cf[0];

                try {
                    if ($cf["workflow"]) {
                        $sth = $this->db->prepare("
                                update
                                    tt_issue_custom_fields
                                set 
                                    field_display = :field_display,
                                    field_description = :field_description,
                                    link = :link
                                where
                                    issue_custom_field_id = $customFieldId
                            ");
                        $sth->execute([
                            ":field_display" => $fieldDisplay,
                            ":field_description" => $fieldDescription,
                            ":link" => $link,
                        ]);

                        $upd = $this->db->prepare("update tt_issue_custom_fields_options set option_display = :display where issue_custom_field_id = $customFieldId and issue_custom_field_option_id = :option");

                        foreach ($options as $option => $display) {
                            if (!checkInt($option)) {
                                return false;
                            }
                            $upd->execute([
                                ":option" => $option,
                                ":display" => $display,
                            ]);
                        }
                    } else {
                        $sth = $this->db->prepare("
                            update
                                tt_issue_custom_fields
                            set 
                                field_display = :field_display,
                                field_description = :field_description,
                                regex = :regex,
                                link = :link,
                                format = :format
                            where
                                issue_custom_field_id = $customFieldId
                        ");

                        $sth->execute([
                            ":field_display" => $fieldDisplay,
                            ":field_description" => $fieldDescription,
                            ":regex" => $regex,
                            ":link" => $link,
                            ":format" => $format,
                        ]);

                        if ($cf["type"] === "Select" || $cf["type"] === "MultiSelect") {
                            $t = explode("\n", trim($options));
                            $new = [];
                            foreach ($t as $i) {
                                $i = trim($i);
                                if ($i) {
                                    $new[] = $i;
                                }
                            }

                            $ins = $this->db->prepare("insert into tt_issue_custom_fields_options (issue_custom_field_id, option, option_display) values ($customFieldId, :option, :option)");
                            $del = $this->db->prepare("delete from tt_issue_custom_fields_options where issue_custom_field_id = $customFieldId and option = :option");
                            $upd = $this->db->prepare("update tt_issue_custom_fields_options set option_display = :option, display_order = :order where issue_custom_field_id = $customFieldId and option = :option");

                            $options = $this->db->query("select option from tt_issue_custom_fields_options where issue_custom_field_id = $customFieldId", \PDO::FETCH_ASSOC)->fetchAll();
                            $old = [];
                            foreach ($options as $option) {
                                $old[] = $option["option"];
                            }

                            foreach ($old as $j) {
                                $f = false;
                                foreach ($new as $i) {
                                    if ($i == $j) {
                                        $f = true;
                                        break;
                                    }
                                }
                                if (!$f) {
                                    $del->execute([
                                        ":option" => $j,
                                    ]);
                                }
                            }

                            foreach ($new as $j) {
                                $f = false;
                                foreach ($old as $i) {
                                    if ($i == $j) {
                                        $f = true;
                                        break;
                                    }
                                }
                                if (!$f) {
                                    $ins->execute([
                                        ":option" => $j,
                                    ]);
                                }
                            }

                            $n = 1;
                            foreach ($new as $j) {
                                $upd->execute([
                                    ":option" => $j,
                                    ":order" => $n,
                                ]);
                                $n++;
                            }

                        }
                    }
                } catch (\Exception $e) {
                    error_log(print_r($e, true));
                    return false;
                }

                return true;
            }

            /**
             * @inheritDoc
             */
            public function deleteCustomField($customFieldId)
            {
                if (!checkInt($customFieldId)) {
                    return false;
                }

                $cf = $this->db->query("select * from tt_issue_custom_fields where issue_custom_field_id = $customFieldId", \PDO::FETCH_ASSOC)->fetchAll();
                if (count($cf) !== 1) {
                    return false;
                }
                $cf = $cf[0];

                if ($cf["workflow"]) {
                    return false;
                } else {
                    try {
                        $this->db->exec("delete from tt_issue_custom_fields where issue_custom_field_id = $customFieldId");
                        $this->db->exec("delete from tt_issue_custom_fields_values where issue_custom_field_id = $customFieldId");
                        $this->db->exec("delete from tt_issue_custom_fields_options where issue_custom_field_id = $customFieldId");
                        return true;
                    } catch (\Exception $e) {
                        error_log(print_r($e, true));
                        return false;
                    }
                }
            }

            /**
             * @inheritDoc
             */
            public function getTags()
            {
                // TODO: Implement getTags() method.
            }

            /**
             * @inheritDoc
             */
            public function searchIssues($by, $query)
            {
                // TODO: Implement searchIssues() method.
            }

            /**
             * @inheritDoc
             */
            public function availableFilters($projectId)
            {
                // TODO: Implement availableFilters() method.
            }
        }
    }
