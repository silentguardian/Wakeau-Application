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

function application_main()
{
	global $core;

	$actions = array('slist', 'sedit', 'sdelete', 'plist', 'pedit', 'pdelete', 'pfinalist', 'stats');

	$core['current_action'] = 'slist';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function application_slist()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT
			id_sapplication, id_user, first_name, last_name,
			class, category, supervisor, school, time
		FROM sapplication
		ORDER BY category, school, class, last_name");
	$template['applications'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['applications'][] = array(
			'id' => $row['id_sapplication'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'class' => $row['class'],
			'category' => $row['category'],
			'supervisor' => $row['supervisor'],
			'school' => $row['school'],
			'time' => format_time($row['time']),
			'can_moderate' => $user['admin'] || $row['id_user'] == $user['id'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Student Application List';
	$core['current_template'] = 'application_slist';
}

function application_sedit()
{
	global $core, $template, $user;

	$id_application = !empty($_REQUEST['application']) ? (int) $_REQUEST['application'] : 0;
	$is_new = empty($id_application);

	$template['categories'] = array(
	);

	if ($is_new)
	{
		$template['application'] = array(
			'is_new' => true,
			'id' => 0,
			'first_name' => '',
			'last_name' => '',
			'class' => '',
			'category' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT
				id_sapplication, first_name, last_name,
				class, category, supervisor, school, time
			FROM sapplication
			WHERE id_sapplication = $id_application" . ($user['admin'] ? "" : "
				AND id_user = $user[id]") . "
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['application'] = array(
				'is_new' => false,
				'id' => $row['id_sapplication'],
				'first_name' => $row['first_name'],
				'last_name' => $row['last_name'],
				'class' => $row['class'],
				'category' => $row['category'],
			);
		}
		db_free_result($request);

		if (!isset($template['application']))
			fatal_error('The application requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('application');

		$values = array();
		$fields = array(
			'first_name' => 'string',
			'last_name' => 'string',
			'class' => 'string',
			'category' => 'string',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
		}

		if ($values['first_name'] === '')
			fatal_error('First name field cannot be empty!');
		elseif ($values['last_name'] === '')
			fatal_error('Last name field cannot be empty!');
		elseif ($values['class'] === '')
			fatal_error('Class field cannot be empty!');
		elseif ($values['category'] === '')
			fatal_error('Category field cannot be empty!');
		elseif (!in_array($values['category'], $template['categories']))
			fatal_error('Category field provided is not valid!');

		if ($is_new)
		{
			$teachers = array(
			);

			$insert = array(
				'id_user' => $user['id'],
				'time' => time(),
				'supervisor' => "'" . $teachers[$user['id']][0] . "'",
				'school' => "'" . $teachers[$user['id']][1] . "'",
			);

			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO sapplication
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE sapplication
				SET " . implode(', ', $update) . "
				WHERE id_sapplication = $id_application
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url(array('application', 'slist')));

	$template['page_title'] = (!$is_new ? 'Edit Application' : 'Apply');
	$core['current_template'] = 'application_sedit';
}

function application_sdelete()
{
	global $core, $template, $user;

	$id_application = !empty($_REQUEST['application']) ? (int) $_REQUEST['application'] : 0;

	$request = db_query("
		SELECT id_sapplication, id_user
		FROM sapplication
		WHERE id_sapplication = $id_application
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$id_user = $row['id_user'];

		$template['application'] = array('id' => $row['id_sapplication']);
	}
	db_free_result($request);

	if (!isset($template['application']))
		fatal_error('The application requested does not exist!');
	elseif (!$user['admin'] && $user['id'] != $id_user)
		fatal_error('You are not allowed to carry out this action!');

	if (!empty($_POST['delete']))
	{
		check_session('application');

		db_query("
			DELETE FROM sapplication
			WHERE id_sapplication = $id_application
			LIMIT 1");
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url(array('application', 'slist')));

	$template['page_title'] = 'Delete Student Application';
	$core['current_template'] = 'application_sdelete';
}

function application_plist()
{
	global $core, $template, $user;

	$request = db_query("
		SELECT
			id_papplication, id_user, first_name, last_name,
			class, category, supervisor, school, time, finalist
		FROM papplication
		ORDER BY category, school, class, last_name");
	$template['applications'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['applications'][] = array(
			'id' => $row['id_papplication'],
			'first_name' => $row['first_name'],
			'last_name' => $row['last_name'],
			'class' => $row['class'],
			'category' => $row['category'],
			'supervisor' => $row['supervisor'],
			'school' => $row['school'],
			'time' => format_time($row['time']),
			'finalist' => $row['finalist'],
			'can_moderate' => $user['admin'] || $row['id_user'] == $user['id'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Project Application List';
	$core['current_template'] = 'application_plist';
}

function application_pedit()
{
	global $core, $template, $user;

	$id_application = !empty($_REQUEST['application']) ? (int) $_REQUEST['application'] : 0;
	$is_new = empty($id_application);

	$template['categories'] = array(
	);

	if ($is_new)
	{
		$template['application'] = array(
			'is_new' => true,
			'id' => 0,
			'first_name' => '',
			'last_name' => '',
			'class' => '',
			'category' => '',
		);
	}
	else
	{
		$request = db_query("
			SELECT
				id_papplication, first_name, last_name,
				class, category, supervisor, school, time
			FROM papplication
			WHERE id_papplication = $id_application" . ($user['admin'] ? "" : "
				AND id_user = $user[id]") . "
			LIMIT 1");
		while ($row = db_fetch_assoc($request))
		{
			$template['application'] = array(
				'is_new' => false,
				'id' => $row['id_papplication'],
				'first_name' => $row['first_name'],
				'last_name' => $row['last_name'],
				'class' => $row['class'],
				'category' => $row['category'],
			);
		}
		db_free_result($request);

		if (!isset($template['application']))
			fatal_error('The application requested does not exist!');
	}

	if (!empty($_POST['save']))
	{
		check_session('application');

		$values = array();
		$fields = array(
			'first_name' => 'string',
			'last_name' => 'string',
			'class' => 'string',
			'category' => 'string',
		);

		foreach ($fields as $field => $type)
		{
			if ($type === 'string')
				$values[$field] = !empty($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES) : '';
		}

		if ($values['first_name'] === '')
			fatal_error('First name field cannot be empty!');
		elseif ($values['last_name'] === '')
			fatal_error('Last name field cannot be empty!');
		elseif ($values['class'] === '')
			fatal_error('Class field cannot be empty!');
		elseif ($values['category'] === '')
			fatal_error('Category field cannot be empty!');
		elseif (!in_array($values['category'], $template['categories']))
			fatal_error('Category field provided is not valid!');
		elseif ($is_new && (empty($_FILES['file']) || empty($_FILES['file']['name'])))
			fatal_error('You need to upload a file!');

		$teachers = array(
		);

		if ($is_new)
		{
			$insert = array(
				'id_user' => $user['id'],
				'time' => time(),
				'supervisor' => "'" . $teachers[$user['id']][0] . "'",
				'school' => "'" . $teachers[$user['id']][1] . "'",
			);

			foreach ($values as $field => $value)
				$insert[$field] = "'" . $value . "'";

			db_query("
				INSERT INTO papplication
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");

			$id_application = db_insert_id();
		}
		else
		{
			$update = array();
			foreach ($values as $field => $value)
				$update[] = $field . " = '" . $value . "'";

			db_query("
				UPDATE papplication
				SET " . implode(', ', $update) . "
				WHERE id_papplication = $id_application
				LIMIT 1");
		}

		if (!empty($_FILES['file']) && !empty($_FILES['file']['name']))
		{
			$file_alias = strtolower($teachers[$user['id']][1]) . '_' . $id_application . '_' . strtolower($values['last_name']) . '_' . strtolower($values['first_name']);
			$file_size = (int) $_FILES['file']['size'];
			$file_extension = htmlspecialchars(strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1)), ENT_QUOTES);
			$file_dir = strtolower($values['category'][0]) . '/' . $file_alias . '.' . $file_extension;

			if (!is_uploaded_file($_FILES['file']['tmp_name']) || (@ini_get('open_basedir') == '' && !file_exists($_FILES['file']['tmp_name'])))
				fatal_error('File could not be uploaded!');

			if ($file_size > 5 * 1024 * 1024)
				fatal_error('Files cannot be larger than 5 MB!');

			if (!in_array($file_extension, array('doc', 'docx')))
				fatal_error('Only files with the following extensions can be uploaded: ' . implode(', ', array('doc', 'docx')));

			if (!move_uploaded_file($_FILES['file']['tmp_name'], $core['site_dir'] . '/application/' . $file_dir))
				fatal_error('File could not be uploaded!');

			db_query("
				UPDATE papplication
				SET file = '$file_dir'
				WHERE id_papplication = $id_application
				LIMIT 1");
		}
	}

	if (!empty($_POST['save']) || !empty($_POST['cancel']))
		redirect(build_url(array('application', 'plist')));

	$template['page_title'] = (!$is_new ? 'Edit Application' : 'Apply');
	$core['current_template'] = 'application_pedit';
}

function application_pdelete()
{
	global $core, $template, $user;

	$id_application = !empty($_REQUEST['application']) ? (int) $_REQUEST['application'] : 0;

	$request = db_query("
		SELECT id_papplication, id_user, file
		FROM papplication
		WHERE id_papplication = $id_application
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$id_user = $row['id_user'];
		$file_dir = $row['file'];

		$template['application'] = array('id' => $row['id_papplication']);
	}
	db_free_result($request);

	if (!isset($template['application']))
		fatal_error('The application requested does not exist!');
	elseif (!$user['admin'] && $user['id'] != $id_user)
		fatal_error('You are not allowed to carry out this action!');

	if (!empty($_POST['delete']))
	{
		check_session('application');

		db_query("
			DELETE FROM papplication
			WHERE id_papplication = $id_application
			LIMIT 1");

		@unlink($core['site_dir'] . '/application/' . $file_dir);
	}

	if (!empty($_POST['delete']) || !empty($_POST['cancel']))
		redirect(build_url(array('application', 'plist')));

	$template['page_title'] = 'Delete Project Application';
	$core['current_template'] = 'application_pdelete';
}

function application_pfinalist()
{
	global $core, $template, $user;

	$id_application = !empty($_REQUEST['application']) ? (int) $_REQUEST['application'] : 0;

	$request = db_query("
		SELECT id_papplication, id_user, file
		FROM papplication
		WHERE id_papplication = $id_application
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$id_user = $row['id_user'];
		$file_dir = $row['file'];

		$template['application'] = array('id' => $row['id_papplication']);
	}
	db_free_result($request);

	if (!isset($template['application']))
		fatal_error('The application requested does not exist!');
	elseif (!$user['admin'] && $user['id'] != $id_user)
		fatal_error('You are not allowed to carry out this action!');

	if (!empty($_POST['finalist']))
	{
		check_session('application');

		db_query("
			UPDATE papplication
			SET finalist = CASE WHEN finalist = 0 THEN 1 ELSE 0 END
			WHERE id_papplication = $id_application
			LIMIT 1");
	}

	if (!empty($_POST['finalist']) || !empty($_POST['cancel']))
		redirect(build_url(array('application', 'plist')));

	$template['page_title'] = 'Change Project Finalist State';
	$core['current_template'] = 'application_pfinalist';
}

function application_stats()
{
	global $core, $template, $user;

	$template['categories'] = array(
	);

	$template['schools'] = array(
	);

	$template['teachers'] = array(
	);

	$template['data'] = array(
		'teachers' => array(),
		'schools' => array(),
		'finalists' => array(),
	);

	$request = db_query("
		SELECT id_user, category, school
		FROM sapplication");
	while ($row = db_fetch_assoc($request))
	{
		if (!isset($template['data']['teachers'][$row['id_user']][$row['category'][0]]))
			$template['data']['teachers'][$row['id_user']][$row['category'][0]] = 0;
		if (!isset($template['data']['schools'][$row['school']][$row['category'][0]]))
			$template['data']['schools'][$row['school']][$row['category'][0]] = 0;

		$template['data']['teachers'][$row['id_user']][$row['category'][0]]++;
		$template['data']['schools'][$row['school']][$row['category'][0]]++;
	}
	db_free_result($request);

	$request = db_query("
		SELECT id_user, category, school, finalist
		FROM papplication");
	while ($row = db_fetch_assoc($request))
	{
		if (!isset($template['data']['teachers'][$row['id_user']][$row['category'][0]]))
			$template['data']['teachers'][$row['id_user']][$row['category'][0]] = 0;
		if (!isset($template['data']['schools'][$row['school']][$row['category'][0]]))
			$template['data']['schools'][$row['school']][$row['category'][0]] = 0;
		if (!isset($template['data']['finalists'][$row['school']][$row['category'][0]]))
			$template['data']['finalists'][$row['school']][$row['category'][0]] = 0;

		$template['data']['teachers'][$row['id_user']][$row['category'][0]]++;
		$template['data']['schools'][$row['school']][$row['category'][0]]++;
		if (!empty($row['finalist']))
			$template['data']['finalists'][$row['school']][$row['category'][0]]++;
	}
	db_free_result($request);

	$template['page_title'] = 'Application Stats';
	$core['current_template'] = 'application_stats';
}