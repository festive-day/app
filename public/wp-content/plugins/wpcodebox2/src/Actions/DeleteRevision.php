<?php


namespace Wpcb2\Actions;


use Wpcb2\Api\Api;
use Wpcb2\Repository\RevisionsRepository;
use Wpcb2\Service\SCSSUpdater;


class DeleteRevision
{
    public function execute($id)
    {
		$revisionRepository = new RevisionsRepository();

		$revision = $revisionRepository->deleteRevision($id);

		$revisions = $revisionRepository->getRevisions($revision['snippet_id']);

		echo json_encode($revisions);
        die;
    }
}
