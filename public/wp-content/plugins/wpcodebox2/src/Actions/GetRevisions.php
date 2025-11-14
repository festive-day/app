<?php


namespace Wpcb2\Actions;


use Wpcb2\Repository\SnippetRepository;
use Wpcb2\Service\SnippetToResponseMapper;

class GetRevisions
{
    public function execute($id)
    {
		$revisionRepository = new \Wpcb2\Repository\RevisionsRepository();
		$revisions = $revisionRepository->getRevisions($id);

        echo json_encode($revisions);
        die;
    }
}
