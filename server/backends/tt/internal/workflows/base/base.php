<?php

    namespace tt\workflow {

        class base extends workflow {

            /**
             * @inheritDoc
             */
            public function initProject($projectId)
            {
                error_log("******* BASE *************" . $projectId . "######################");
                return true;
            }

            /**
             * @inheritDoc
             */
            public function initIssue($issueId)
            {
                error_log("------- BASE -------------" . $issueId . "++++++++++++++++++++++");
                return true;
            }

            /**
             * @inheritDoc
             */
            public function createIssueTemplate()
            {
                // TODO: Implement createIssueTemplate() method.
            }

            public function availableActions($issueId)
            {
                // TODO: Implement availableActions() method.
            }

            public function actionTemplate($issueId, $action)
            {
                // TODO: Implement actionTemplate() method.
            }

            public function progressAction($issueId, $action, $fields)
            {
                // TODO: Implement progressAction() method.
            }
        }
    }