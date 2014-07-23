<?php

/**
 * @package Wakeau
 *
 * @author Selman Eser
 * @copyright 2014 Selman Eser
 * @license BSD 2-clause
 *
 * @version 1.0
 */

if (!defined('CORE'))
	exit();

function template_application_slist()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-info" href="', build_url(array('application', 'stats')), '">Application Stats</a>
				<a class="btn" href="', build_url(array('application', 'plist')), '">Project Applications</a>
				<a class="btn btn-warning" href="', build_url(array('application', 'sedit')), '">Apply</a>
			</div>
			<h2>Student Application List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Class</th>
					<th>Category</th>
					<th>Supervisor</th>
					<th>School</th>
					<th>Time</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['applications']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="8">There are not any applications yet!</td>
				</tr>';
	}

	foreach ($template['applications'] as $application)
	{
		echo '
				<tr>
					<td>', $application['first_name'], '</td>
					<td>', $application['last_name'], '</td>
					<td class="span1 align_center">', $application['class'], '</td>
					<td class="span3 align_center">', $application['category'], '</td>
					<td>', $application['supervisor'], '</td>
					<td class="span1 align_center">', $application['school'], '</td>
					<td class="span2 align_center">', $application['time'], '</td>
					<td class="span3 align_center">';

		if ($application['can_moderate'])
		{
			echo '
						<a class="btn btn-primary" href="', build_url(array('application', 'sedit', $application['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('application', 'sdelete', $application['id'])), '">Delete</a>';
		}

		echo '
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_application_sedit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('application', 'sedit')), '" method="post">
			<fieldset>
				<legend>', (!$template['application']['is_new'] ? 'Edit Application' : 'Apply'), '</legend>
				<div class="control-group">
					<label class="control-label" for="first_name">First name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="first_name" name="first_name" value="', $template['application']['first_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_name">Last name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="last_name" name="last_name" value="', $template['application']['last_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="class">Class:</label>
					<div class="controls">
						<input type="text" class="input-mini" id="class" name="class" value="', $template['application']['class'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="category">Category:</label>
					<div class="controls">
						<select id="category" name="category">
							<option value="">Select category</option>';

	foreach ($template['categories'] as $category)
	{
		echo '
							<option value="', $category, '"', ($template['application']['category'] == $category ? ' selected="selected"' : ''), '>', $category, '</option>';
	}

	echo '
						</select>
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="application" value="', $template['application']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_application_sdelete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('application', 'sdelete')), '" method="post">
			<fieldset>
				<legend>Delete Student Application</legend>
				Are you sure you want to delete the selected application?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="application" value="', $template['application']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_application_plist()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn btn-info" href="', build_url(array('application', 'stats')), '">Application Stats</a>
				<a class="btn" href="', build_url(array('application', 'slist')), '">Student Applications</a>
				<a class="btn btn-warning" href="', build_url(array('application', 'pedit')), '">Apply</a>
			</div>
			<h2>Project Application List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Class</th>
					<th>Category</th>
					<th>Supervisor</th>
					<th>School</th>
					<th>Time</th>
					<th>Finalist</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['applications']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="9">There are not any applications yet!</td>
				</tr>';
	}

	foreach ($template['applications'] as $application)
	{
		echo '
				<tr>
					<td>', $application['first_name'], '</td>
					<td>', $application['last_name'], '</td>
					<td class="span1 align_center">', $application['class'], '</td>
					<td class="span3 align_center">', $application['category'], '</td>
					<td>', $application['supervisor'], '</td>
					<td class="span1 align_center">', $application['school'], '</td>
					<td class="span2 align_center">', $application['time'], '</td>
					<td class="span1 align_center">', $application['finalist'] ? 'Yes' : 'No', '</td>
					<td class="align_center">';

		if ($application['can_moderate'])
		{
			echo '
						<a class="btn btn-', $application['finalist'] ? 'info' : 'warning', '" href="', build_url(array('application', 'pfinalist', $application['id'])), '">', $application['finalist'] ? 'Undo Finalist' : 'Make Finalist', '</a>
						<a class="btn btn-primary" href="', build_url(array('application', 'pedit', $application['id'])), '">Edit</a>
						<a class="btn btn-danger" href="', build_url(array('application', 'pdelete', $application['id'])), '">Delete</a>';
		}

		echo '
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_application_pedit()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('application', 'pedit')), '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>', (!$template['application']['is_new'] ? 'Edit Application' : 'Apply'), '</legend>
				<div class="control-group">
					<label class="control-label" for="first_name">First name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="first_name" name="first_name" value="', $template['application']['first_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="last_name">Last name:</label>
					<div class="controls">
						<input type="text" class="input-xlarge" id="last_name" name="last_name" value="', $template['application']['last_name'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="class">Class:</label>
					<div class="controls">
						<input type="text" class="input-mini" id="class" name="class" value="', $template['application']['class'], '" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="category">Category:</label>
					<div class="controls">
						<select id="category" name="category">
							<option value="">Select category</option>';

	foreach ($template['categories'] as $category)
	{
		echo '
							<option value="', $category, '"', ($template['application']['category'] == $category ? ' selected="selected"' : ''), '>', $category, '</option>';
	}

	echo '
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="file">', (!$template['application']['is_new'] ? 'Replace' : 'Select'), ' file:</label>
					<div class="controls">
						<input type="file" class="input-xlarge" id="file" name="file" />
					</div>
				</div>
				<div class="form-actions">
					<input type="submit" class="btn btn-primary" name="save" value="Save changes" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="application" value="', $template['application']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_application_pdelete()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('application', 'pdelete')), '" method="post">
			<fieldset>
				<legend>Delete Project Application</legend>
				Are you sure you want to delete the selected application?
				<div class="form-actions">
					<input type="submit" class="btn btn-danger" name="delete" value="Delete" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="application" value="', $template['application']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_application_pfinalist()
{
	global $user, $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('application', 'pfinalist')), '" method="post">
			<fieldset>
				<legend>Change Project Finalist State</legend>
				Are you sure you want to change the application finalist state?
				<div class="form-actions">
					<input type="submit" class="btn btn-warning" name="finalist" value="Change" />
					<input type="submit" class="btn" name="cancel" value="Cancel" />
				</div>
			</fieldset>
			<input type="hidden" name="application" value="', $template['application']['id'], '" />
			<input type="hidden" name="session_id" value="', $user['session_id'], '" />
		</form>';
}

function template_application_stats()
{
	global $template;

	echo '
		<div class="page-header">
			<div class="pull-right">
				<a class="btn" href="', build_url(array('application', 'plist')), '">Project Applications</a>
				<a class="btn" href="', build_url(array('application', 'slist')), '">Student Applications</a>
			</div>
			<h2>Application Stats</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Teacher</th>';

	foreach ($template['categories'] as $category)
	{
		echo '
					<th>', $category[0], '</th>';
	}

	echo '
					<th>Project Total</th>
				</tr>
			</thead>
			<tbody>';

	foreach ($template['teachers'] as $id => $name)
	{
		$total = 0;

		echo '
				<tr>
					<td>', $name, '</td>';

		foreach ($template['categories'] as $category)
		{
			$count = !empty($template['data']['teachers'][$id][$category[0]]) ? $template['data']['teachers'][$id][$category[0]] : 0;
			if (in_array($category[0], array()))
				$total += $count;

			echo '
					<td class="align_center" style="width: 80px;">', $count, '</td>';
		}

		echo '
					<td class="span2 align_center">', $total, '</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>
		<br />
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>School</th>';

	foreach ($template['categories'] as $category)
	{
		echo '
					<th>', $category[0], '</th>';
	}

	echo '
					<th>Project Total</th>
				</tr>
			</thead>
			<tbody>';

	foreach ($template['schools'] as $school)
	{
		$total = 0;

		echo '
				<tr>
					<td>', $school, '</td>';

		foreach ($template['categories'] as $category)
		{
			$count = !empty($template['data']['schools'][$school][$category[0]]) ? $template['data']['schools'][$school][$category[0]] : 0;
			$finalists = '';
			if (in_array($category[0], array()))
			{
				$finalists = ' (' . (!empty($template['data']['finalists'][$school][$category[0]]) ? $template['data']['finalists'][$school][$category[0]] : 0) . ')';
				$total += $count;
			}

			echo '
					<td class="align_center" style="width: 80px;">', $count . $finalists, '</td>';
		}

		echo '
					<td class="span2 align_center">', $total, '</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}