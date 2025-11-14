<?php


namespace Wpcb2\Actions;


use Wpcb2\Api\Api;
use Wpcb2\Repository\RevisionsRepository;
use Wpcb2\Service\SCSSUpdater;


class NoteRevision
{
    public function execute($revisionId)
    {
		$body = file_get_contents("php://input");
		$data = json_decode($body, true);

		$revisionRepository = new RevisionsRepository();
		$revision = $revisionRepository->setRevisionNote($revisionId, $data['note']);

		$revisions = $revisionRepository->getRevisions($revision['snippet_id']);
		echo json_encode($revisions);
        die;
    }
}
