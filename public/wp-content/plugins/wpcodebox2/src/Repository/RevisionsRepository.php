<?php

namespace Wpcb2\Repository;


class RevisionsRepository
{
	private $wpdb;

	public function __construct()
	{

		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * @param $revisionId
	 * @return array Revision
	 */
	public function toggleRevisionStar($revisionId)
	{
		$query = "UPDATE {$this->wpdb->prefix}wpcb_revisions SET star = !star WHERE id = %d";
		$this->wpdb->query($this->wpdb->prepare($query, $revisionId));

		$revision = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}wpcb_revisions WHERE id = %d", $revisionId), ARRAY_A);

		return $revision;
	}

	public function setRevisionNote($revisionId, $note)
	{
		$query = "UPDATE {$this->wpdb->prefix}wpcb_revisions SET note = %s WHERE id = %d";
		$this->wpdb->query($this->wpdb->prepare($query, $note, $revisionId));

		$revision = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}wpcb_revisions WHERE id = %d", $revisionId), ARRAY_A);

		return $revision;
	}

	public function getRevisions($snippetId)
	{
		$query = "SELECT * FROM {$this->wpdb->prefix}wpcb_revisions WHERE snippet_id = %d ORDER BY time DESC";
		$revisions = $this->wpdb->get_results($this->wpdb->prepare($query, $snippetId), ARRAY_A);

		foreach($revisions as &$revision) {
			$revision['time'] = date('M d, Y H:i:s', $revision['time']);
		}

		return $revisions;
	}

	public function deleteRevision($revisionId)
	{
		$revision = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->wpdb->prefix}wpcb_revisions WHERE id = %d", $revisionId), ARRAY_A);

		$query = "DELETE FROM {$this->wpdb->prefix}wpcb_revisions WHERE id = %d";
		$this->wpdb->query($this->wpdb->prepare($query, $revisionId));
		return $revision;
	}

	public function saveRevision($snippetId, $oldCode, $newCode)
	{
		if($oldCode === $newCode) {
			return false;
		}

		$revision = [];
		$revision['snippet_id'] = $snippetId;
		$revision['old_code'] = $oldCode;
		$revision['time'] = time();

		$maxRevisions = get_option('wpcb_number_of_revisions', 10);
		if($maxRevisions <= 0) {
			return false;
		}

		$this->wpdb->insert($this->wpdb->prefix . 'wpcb_revisions', $revision);

		// Delete revisions if there are more than $maxRevisions
		$query = "SELECT id FROM {$this->wpdb->prefix}wpcb_revisions WHERE snippet_id = %d ORDER BY `time` DESC LIMIT %d, 100";
		$query = $this->wpdb->prepare($query, $snippetId, $maxRevisions);
		$revisionsToDelete = $this->wpdb->get_col($query);

		foreach($revisionsToDelete as $revisionId) {
			$this->deleteRevision($revisionId);
		}


		return $this->wpdb->insert_id;
	}


}
