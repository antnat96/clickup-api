<?php
/**
 * ClickUp  
 * This module is being used for clickup related functionality.
 * @ClickUp
 * @Anthony Natale
 * @Created 5/30/2018
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class clickup extends MY_Controller {

/**
 *Call constructor.
 *Created on Date June 1 2018
 *@author		Anthony Natale
 */
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('file');
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->library('ftp');
		$this->load->library('upload');
  	$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
	}

/**
 *Opens ClickUp Index view
 *Created on Date June 1 2018
 *@author		Anthony Natale
 */
	public function index()
	{
		$data = array();

		// Set page title
		$this->config->set_item('title', 'Tools');

		// Check page log info and update it
		$this->updateSessionLog();

		// Get session values
		$userInfo = $this->session->userdata('account');

		// Create the 'find' command and specify the layout
		$findCommand =& $this->fm->newFindCommand('CWPIndividuals');

		// Specify the field and value to match against.
		$findCommand->addFindCriterion('PrimaryEmail', 	"==" . $this->account['PrimaryEmail'] );

		// Execute the command for result
		$result = $findCommand->execute();

        // Check for an error
        if (FileMaker::isError($result)) {
            $data['errorMsg'] = $result->getMessage();
        }
        else
        {
	    	// Store the matching records
	        $records = $result->getRecords();
			$record  = $records[0];

			// Getting related portals of current layout
	 		$relatedSetAccount 		= $record->getRelatedSet('AcctsForInds');

			// Check for an error
			if (FileMaker::isError($relatedSetAccount)) {
				$data['errorMsg'] = "";
			}
			else
			{
				// Set the related set data for view
				$data['accountsInfo'] 	   = $relatedSetAccount;
			}
		}

		// Get user info from session
		$data['userInfo'] = $userInfo;

		//set the breadcrumb array values
		$breadcrumbs = array(
			'Tools' => ''
			);

		//Assign the data for the view.
		$data['breadcrumbs'] = $breadcrumbs;
		$data['template'] = 'clickup/allTabs';

		//load the data for template view
		$this->load->vars($data);
		$this->load->view('template');
	}

/*
* buildHeader function
* This function will return the API authorization header and cuts lines from this document
* @Author: Anthony Natale
* @Date: 11/12/2018
*/    
    public function buildHeader() {
        // FileMaker call for API Key	
		$findCommand =& $this->fm->newFindCommand('CWPIndividuals');
 		$findCommand->addFindCriterion('PrimaryEmail', 	"==" . $this->account['PrimaryEmail']);
  		// Execute the command for result 
 		$result = $findCommand->execute();
		//Check for an error
		if (FileMaker::isError($result)) {
            echo "error";
			return;
		}
        
        // Authorization header
        $records = $result->getRecords();
        $record  = $records[0];
        $clickUpAPIKey = $record->getField('ClickUpAPIKey'); // Store the API Key
		
        // Build authorization header w/ key
        $authorizationHeader=$clickUpAPIKey;
        $authorizationHeader="Authorization: " . $authorizationHeader;
        if (isset($authorizationHeader)) { return $authorizationHeader; 
        }
    }  
    
/*
* getKey function
* This function will return the API key for the current user to the functions.js function "authTest" and will make sure it works
* @Author: Anthony Natale
* @Date: 9/11/2018
*/    
    public function getKey() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();       
        
        // Checking if it's a good key
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/user");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          $authorizationHeader
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (strpos($response, "err") !== false) {
            echo "bad";
        }
        else {
            echo "good";
        }
    }
/*
* fixKey function
* This function will call the fixKey view
* @Author: Anthony Natale
* @Date: 9/11/2018
*/
    public function fixKey() {
        // Setting the breadcrumbs
        $breadcrumbs = array('Tools > ClickUp' => '');
        $template = 'clickup/authTest';
        //Assign the data for the view.
        $data['breadcrumbs'] = $breadcrumbs;
        $data['template'] = $template;
        $this->load->vars($data);
        $this->load->view('template');
    }

/*
* tasks() function
* This function gets all the important information for users and tasks from ClickUp and passes to view
* Works down the ClickUp hierarchy-user, teams, tasks using nested loops
* @Author: Anthony Natale
* @Date: 6/20/2018
*/
 	public function tasks() { 
        // Setting the breadcrumbs
        $breadcrumbs = array('Tools > ClickUp' => '');
        $template = 'clickup/allTabs';
		//Assign the data for the view.
		$data['breadcrumbs'] = $breadcrumbs;
        $data['template'] = $template;
    
    // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
    // Teams
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $decoded = json_decode($response, true); // array
        $numOfTeams = count($decoded['teams']);
        $data['numOfTeams'] = $numOfTeams; // total number of teams to pass to view
        $decoded2 = json_decode($response); // object
        $data['getUsers'] = $decoded2;
        
        if (isset($numOfTeams) && isset($decoded['teams'][0]['name'])) {
            for ($i = 0; $i < $numOfTeams; $i++) {
                $teamIDs[$i] = $decoded['teams'][$i]['id']; // team ID
                $teamNames[$i] = $decoded['teams'][$i]['name']; // team name
                }
            $data['teamNames'] = $teamNames; // team names array
            $data['teamIDs'] = $teamIDs; // team ids array
        
        // Spaces, checking to see if there are any in the team
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/". $teamIDs[0] . "/space"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $authorizationHeader
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            $decoded = json_decode($response, true); 
            if (isset($decoded['spaces'][0]['id'])) {
                $spaceID[0] = $decoded['spaces'][0]['id']; // first space ID
            }
            else {
                $data['error'] = "Error completing SPACE data API call";
            }
            
        // Spaces for statuses
            for ($teamsLoop = 0; $teamsLoop < $numOfTeams; $teamsLoop++) { // This allows each team to be looped through to grab the different spaces                                   

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/". $teamIDs[$teamsLoop] . "/space"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $authorizationHeader
            ));
            $response = curl_exec($ch);
            curl_close($ch);
                $decodedObject[$teamsLoop] = json_decode($response, true);
                for ($i = 0; $i < count($decodedObject[$teamsLoop]['spaces']); $i++) { 
                    for ($j = 0; $j < count($decodedObject[$teamsLoop]['spaces'][$i]['statuses']); $j++) { 
                        $statuses[$decodedObject[$teamsLoop]['spaces'][$i]['id']][$j] = $decodedObject[$teamsLoop]['spaces'][$i]['statuses'][$j]['status']; 
                    }
                }
                if (isset($statuses)) {
                    $data['spaceInfo'] = $statuses; 
                }
            }

            if (isset($spaceID)) {
        // Projects and Lists
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/space/" . $spaceID[0] . "/project");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $authorizationHeader
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            $decoded = json_decode($response, true);
            if (isset($decoded['projects'][0]['lists'][0]['id'])) {
                $data['listID'] = $decoded['projects'][0]['lists'][0]['id']; // list ID
            }
            else {
                $data['error'] = "Error completing PROJECTS/LISTS API call";
            }

        // Tasks
            for ($teamsLoop = 0; $teamsLoop < $numOfTeams; $teamsLoop++) { // This allows each team to be looped through to grab the different spaces                                   
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/" . $teamIDs[$teamsLoop] . "/task?subtasks=true&include_closed=true"); // Subtasks are on, this gets all tasks from all team ids discovered earlier
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                $authorizationHeader 
                ));
                $response = curl_exec($ch);
                curl_close($ch);

                $teamObject[$teamsLoop] = json_decode($response); // object
                $data['team'] = $teamObject;
                $decoded[$teamsLoop] = json_decode($response, true); // array
                $tasksEach[$teamsLoop] = count($decoded[$teamsLoop]['tasks']); // # of tasks for a team
                $data['tasksInTeam'] = $tasksEach;
            }
                }
                    }
        
        else {
            $data['error'] = "Error completing TEAM data API call";
        }
        $this->load->vars($data);
        $this->load->view('template');
    }   
    
/**
* Pulls the current API Key from FileMaker and shows it in the "editKey.php" view
* Created on Date June 20 2018
* @author		Anthony Natale
* @param:		na
* @return		na
*/
    public function editAPIKey()
	{
        $data = array();
		//Find the pirticular client project at project layout
		$findCommand =& $this->fm->newFindCommand('CWPIndividuals');
 		$findCommand->addFindCriterion('PrimaryEmail', 	"==" . $this->account['PrimaryEmail'] );

  		// Execute the command for result 
 		$result = $findCommand->execute();

		//Check for an error
		if (FileMaker::isError($result)) {
            echo "error";
			return;
		}
        
        // Get the matching records
        $records = $result->getRecords();
        $record  = $records[0];
        $ClickUpAPIKey = $record->getField('ClickUpAPIKey');
    
		// Store the matching records
        $data['ClickUpAPIKey']=$ClickUpAPIKey;
		$data['rec'] = $record;
        
        $this->load->vars($data);
        $this->load->view('clickup/editKey');
	}
    
/**
* Submit a new API Key to the database from which to pull ClickUp API data
* Created on Date June 20 2018
* @author		Anthony Natale
* @param:		na
* @return		na
*/
 	public function submitAPIKey() {
        //Find the particular client project at project layout
        $findCommand =& $this->fm->newFindCommand('CWPIndividuals');
 		$findCommand->addFindCriterion('PrimaryEmail',"==". $this->account['PrimaryEmail'] );

  		// Execute the command for result 
 		$result = $findCommand->execute();
		
		//Check for an error
		if (FileMaker::isError($result)) {
			redirect('/');
		}
        
		// Store the matching records
        $records = $result->getRecords();
		$record  = $records[0];
	
		$updateAPIKey = $this->fm->newEditCommand('CWPIndividuals', $record->getRecordId());
        $updateAPIKey->setField('ClickUpAPIKey', $_POST['ClickUpAPIKey']); 
		$updateAPIKey->execute();

		if (FileMaker::isError($result)) {
			$this->session->set_flashdata('errorMsg', $result->getMessage());
			redirect('/');
		}
		else{ 
			$this->session->set_flashdata('successMsg', 'API Token submitted successfully.');
			echo 'success';
			return; 
		}
 	}
                                                
/*
* newTaskModal() function
* This function activates a modal to allow the user to create a new ClickUp task in a space, project, list of their choice
* @Author: Anthony Natale
* @Date: 7/2/2018
* @Updated: 9/24/2018
*/
    public function newTaskModal() { 
        
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();         
        // Teams
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team"); // URL to get team info
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader // Full authorization header with API Key
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($response, true); // Array, holds teams and IDs
        $numOfTeams = count($decoded['teams']); // integer, num of teams user is on
        $data['numOfTeams'] = $numOfTeams; // integer passed to view
        $userInfo = json_decode($response); // Object, users and teams names/ids
        if ($numOfTeams > 1) { // If there is more than one team, give the user the select instruction
            $teams[0] = "Select";
        }
        if (isset($numOfTeams)) { // If there are teams
            for ($i = 0; $i < $numOfTeams; $i++) { // go thru each team
                $teamID[$i] = $decoded['teams'][$i]['id']; // get the team ID
                $teamName[$i] = $decoded['teams'][$i]['name']; // get the team ID
                $teams[$teamID[$i]] = $teamName[$i];
            }
            
            $data['teamIDs'] = $teamID; // just IDs
            $data['teams'] = $teams; // teams[12345] = 'team1'
        }
        
        $emptyArray = array("0" => "");
        $data['emptyArray'] = $emptyArray;        
        $data['placeholder1'] = array("1234" => "Space1",
                                      "123" => "Space2");
        $data['placeholder2'] = array("1234" => "Proj1",
                                      "123" => "Proj2");
        $data['placeholder3'] = array("1234" => "List1",
                                      "123" => "List2");
        $data['getUsers'] = $userInfo;
        $this->load->vars($data);
        $this->load->view('clickup/newTask');
    }
    
/*
* getSpaces() function
* Gets all spaces for a team
* @Author: Anthony Natale
* @Date: 11/21/2018
*/    
    public function getSpaces() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();       
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/" . $_POST['teamID'] . "/space");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($response, true); 
        $numOfSpaces = count($decoded['spaces']); 
        if ($numOfSpaces > 1) { // If there is more than one team, give the user the select instruction
            $spaces[0] = "Select";
        }        
            for ($i = 0; $i < $numOfSpaces; $i++) {  
                $spaceID[$i] = $decoded['spaces'][$i]['id']; // get the space id for space $k in team $i
                $spaceName[$i] = $decoded['spaces'][$i]['name']; // get the space name for space $k in team $i
                $spaces[$spaceID[$i]] = $spaceName[$i];         
            }
        if (isset($spaces)) {
            $data['spaces'] = $spaces;
        }
        else {
            $data['spaces'] = "None";
        }
        $this->load->vars($data);
        $this->load->view('clickup/spaces');
        
    }
    
/*
* getProjects() function
* Gets all projects for a team
* @Author: Anthony Natale
* @Date: 11/21/2018
*/    
    public function getProjects() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();       
        
        // Projects and Lists
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/space/" . $_POST['spaceID'] . "/project");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $projAndLists = json_decode($response, true);
        $numOfProj = count($projAndLists['projects']);
        if ($numOfProj > 1) { // If there is more than one team, give the user the select instruction
            $projects[0] = "Select";
        }                  
        for ($i = 0; $i < $numOfProj; $i++) {
                $projID[$i] = $projAndLists['projects'][$i]['id'];
                $projName[$i] = $projAndLists['projects'][$i]['name'];
                $projects[$projID[$i]] = $projName[$i];
            }
        if (isset($projects) && (gettype($projects) == "array")) {
            $data['projects'] = $projects;
        }
        else {
            $data['projects'] = "No projects";
        }
        $this->load->vars($data);
        $this->load->view('clickup/projects');
    } 
    
/*
* getLists() function
* Gets all lists for a team
* @Author: Anthony Natale
* @Date: 11/21/2018
*/    
    public function getLists() {
        if ($_POST['projNum'] < 0) {
            $_POST['projNum'] = 0;
        }
        
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();       
        
        // Projects and Lists
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/space/" . $_POST['spaceID'] . "/project");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $projAndLists = json_decode($response, true);
        $numOfProj = count($projAndLists['projects']);
        $numOfLists = count($projAndLists['projects'][$_POST['projNum']]['lists']);
        
        if ($numOfLists > 1) { // If there is more than one team, give the user the select instruction
            $lists[0] = "Select";
        }            
        for ($i = 0; $i < $numOfLists; $i++) {
            $listID[$i] = $projAndLists['projects']
                [$_POST['projNum']]['lists'][$i]['id'];
            $listName[$i] = $projAndLists['projects']
                [$_POST['projNum']]['lists'][$i]['name'];
            $lists[$listID[$i]] = $listName[$i]; 
        }
        if (isset($lists)) {
            $data['lists'] = $lists;
        }
        else {
            $data['lists'] = "No lists";
        }
        if (isset($data)) {
            $this->load->vars($data);
        }
        $this->load->view('clickup/lists');  
    }     
    
/*
* setListID() function
* Gets all lists for a team
* @Author: Anthony Natale
* @Date: 11/24/2018
*/    
    public function setListID() {

        $data['listID'] = $_POST['listID'];
        $this->load->vars($data);
        $this->load->view('clickup/setListID');  
    }       
    
/*
* getAssignees() function
* Gets all projects for a team
* @Author: Anthony Natale
* @Date: 12/3/2018
*/    
    public function getAssignees() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();       
        
        // Projects and Lists
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/" . $_POST['teamID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $teamInfo = json_decode($response, true);
        $numOfMembers = count($teamInfo["team"]["members"]);
        if ($numOfMembers > 1) { // If there is more than one member assignable, give the user the select instruction
            $assignees[0] = "Select";
        }                  
        for ($i = 0; $i < $numOfMembers; $i++) {
                $assigneeID[$i] = $teamInfo['team']['members'][$i]['user']['id'];
                $assigneeName[$i] = $teamInfo['team']['members'][$i]['user']['username'];
                $assignees[$assigneeID[$i]] = $assigneeName[$i];
            }
        $data['assignees'] = $assignees;
        $this->load->vars($data);
        $this->load->view('clickup/assigneesNewTask');
    }     
    
/*
* newTask() function
* This function makes the ClickUp API call to create the new task
* @Author: Anthony Natale
* @Date: 7/2/2018
*/
    public function newTask() { 
    
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        // Validation
        $newTaskName = $_POST['newTaskName'];
            if ($newTaskName == '') {
                $newTaskName = 'New Task';
            }
            else {
                $newTaskName = htmlentities($newTaskName);
            }
        $newTaskDesc = $_POST['newTaskDesc'];
            if ($newTaskDesc == '') {
                $newTaskDesc = 'None';
            }
            else {
                $newTaskDesc = htmlentities($newTaskDesc);
            } 
        $newTaskAssignee = $_POST['newTaskAssignee'];
                $newTaskAssignee = '';
   
        $newTaskStatus = $_POST['newTaskStatus'];
        
        $newTaskPriority = $_POST['newTaskPriority'];
            if (strpos($newTaskPriority,'0') !== false) {
                $priorityLine = '';
            }
            else {
                $priorityLine = '"priority": "' . $newTaskPriority . '",';
            }
        $newTaskDueDate = strtotime($_POST['newTaskDueDate']); // Converting datepicker time to epoch
            if ($newTaskDueDate == '') {
                $newTaskDueDate = strtotime("+7 day");
            }

        $json = '{
          "name": "' . $newTaskName . '",
          "content": "' . $newTaskDesc . '",
          "assignees": [ ' . $newTaskAssignee . ' ],
          "status": "' . $newTaskStatus . '",
          ' . $priorityLine . '
          "due_date": "' . $newTaskDueDate . "000" . '" }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/list/" . $_POST['newTaskListID'] . "/task");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          $authorizationHeader,
          "Content-Type: Application/json"
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        var_dump($response);
    }
/*
* editTaskModal() function
* This function initiates the edit task modal window
* @Author: Anthony Natale
* @Date: 7/2/2018
*/
    public function editTaskModal() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();

        // team details
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/" . $_POST['teamID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader // Full authorization header with API Key
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $allInfo = json_decode($response); // Getting the object to acquire all the user names and id numbers
        $decoded = json_decode($response, true); // Parses response and allows user to see table of arrays
        
        $alreadyAssigned= array();
        $notYetAssigned = array();
        $numOfMembersOnTeam = count($decoded['team']['members']);
        $data['numOfMembersOnTeam'] = $numOfMembersOnTeam;

        for ($i = 0; $i < $numOfMembersOnTeam; $i++) { // run through each member in the team
            if (strpos($_POST['assigneeIDString'], strval($decoded['team']['members'][$i]['user']['id'])) !== false) { // if user is not assigned, add to assignableUsers array
                $alreadyAssigned[$decoded['team']['members'][$i]['user']['id']] = $decoded['team']['members'][$i]['user']['username'];
            }
            else {
                $notYetAssigned[$decoded['team']['members'][$i]['user']['id']] = $decoded['team']['members'][$i]['user']['username']; // set the key as the ID and the element as the username
            }
        }        

        $data['notYetAssigned'] = $notYetAssigned;
        $data['alreadyAssigned'] = $alreadyAssigned;
        $data['taskID'] = $_POST['taskID'];
        $data['taskName'] = $_POST['taskName'];
        $data['taskDesc'] = $_POST['taskDesc'];
        $data['taskStatus'] = $_POST['taskStatus'];
        $data['taskDueDate'] = $_POST['taskDueDate'];
        $data['taskStatusList'] = explode(",",$_POST['taskStatusList']);
        
        // Task Priority validation
        if (strpos($_POST['taskPriority'], "low") !== false) { $data['taskPriority'] = "4"; }
        else if (strpos($_POST['taskPriority'], "normal") !== false) { $data['taskPriority'] = "3"; }
        else if (strpos($_POST['taskPriority'], "high") !== false) { $data['taskPriority'] = "2"; } 
        else { $data['taskPriority'] = "1"; }

        $this->load->vars($data);
        $this->load->view('clickup/editTask');
    }    
/*
* editTask() function
* This function makes the ClickUp API call to edit and existing task
* @Author: Anthony Natale
* @Date: 7/2/2018
*/
    public function editTask() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        // Task Priority validation
        if (strpos($_POST['taskPriority'], "low") !== false) { $data['taskPriority'] = "4"; }
        else if (strpos($_POST['taskPriority'], "normal") !== false) { $data['taskPriority'] = "3"; }
        else if (strpos($_POST['taskPriority'], "high") !== false) { $data['taskPriority'] = "2"; } 
        else { $data['taskPriority'] = "1"; }
                 
        $_POST['addTaskAssignee'] = trim($_POST['addTaskAssignee']);
        $_POST['removeTaskAssignee'] = trim($_POST['removeTaskAssignee']);
        // Adding and removing assignees validation
        if (strlen($_POST['addTaskAssignee']) > 1) {
            $addAssignee = '"add" :[ ' . str_replace(" ", ",", $_POST['addTaskAssignee']) . ' ]'; 
        } 
        else {
            $addAssignee = ''; 
        }
        if (strlen($_POST['addTaskAssignee']) > 1 && strlen($_POST['removeTaskAssignee']) > 1) {
            $addAssignee = $addAssignee . ','; 
        }
        if (strlen($_POST['removeTaskAssignee']) > 1) {
            $remAssignee = '"rem":[ ' . str_replace(" ", ",", $_POST['removeTaskAssignee']) . ' ]'; 
        } 
        else {
            $remAssignee = ''; 
        }
        
        $json = '{
          "name": "' . $_POST['taskName'] . '",
          "content": "' . $_POST['taskDesc'] . '",
          "assignees": { ' . $addAssignee . $remAssignee . ' },
          "status": "' . $_POST['taskStatus'] . '",
          "priority": "' . $_POST['taskPriority'] . '",
          "due_date": "' . strtotime($_POST['taskDueDate']) . "000" . '" 
          }';
        print_r($json);
        if (isset($_POST['taskID'])) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        }
    }
/*
* clickUpErrorModal() function
* This function generates an error message if the API call returns bad
* @Author: Anthony Natale
* @Date: 10/12/2018
*/    
    public function clickUpErrorModal() {
        $template = 'clickup/clickUpErrorModal';
        $data['errorMessage'] = $_POST['errorMessage'];
        $this->load->vars($data);
        $this->load->view($template);
    }
/*
* deleteTask() function
* This function makes the ClickUp API call to delete an existing task
* NOTE: This function is not currently supported by ClickUp API. This function has been planned to be developed by ClickUp
* @Author: Anthony Natale
* @Date: 7/2/2018
*/
    public function deleteTask() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        /*THIS IS WHERE THE CLICKUP API CALL WILL GO WHEN SUPPORT FOR DELETE TASK IS ADDED*/
        
        $template = 'clickup/deleteTask';
        $data['test'] = "test";
        $this->load->vars($data);
        $this->load->view($template);
    }
    
    
/*
* upgradeStatus() function
* This function makes the ClickUp API call to upgrade the status of an existing task
* @Author: Anthony Natale
* @Date: 9/21/2018
*/
    public function upgradeStatus() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{
          "status": "'. $_POST['upgradeTo'] . '"
        }');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
    }
    
/*
* downgradeStatus() function
* This function makes the ClickUp API call to upgrade the status of an existing task
* @Author: Anthony Natale
* @Date: 9/24/2018
*/
    public function downgradeStatus() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{
          "status": "'. $_POST['downgradeTo'] . '"
        }');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
    }
    
/*
* markTaskComplete() function
* This function makes the ClickUp API call to close out a task
* @Author: Anthony Natale
* @Date: 9/24/2018
*/
    public function markTaskComplete() { 
    
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        // Defining the new task status
        $status = "Closed"; 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{
          "status": "'. $status . '"
        }');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
/*
* removeIndAssignee() function
* This function makes the ClickUp API call to remove an individual assignee
* @Author: Anthony Natale
* @Date: 11/13/2018
*/
    
    public function removeIndAssignee() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        $remAssignee = '"rem":[ ' . $_POST['assigneeID'] . ' ]';
        $json = '{ "assignees": { ' . $remAssignee . ' } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
/*
* addAssigneeModal() function
* This function generates a modal window so the user can choose which assignees to add
* @Author: Anthony Natale
* @Date: 11/15/2018
*/
    
    public function addAssigneeModal() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/team/" . $_POST['teamID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        $authorizationHeader // Full authorization header with API Key
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $allInfo = json_decode($response); // Getting the object to acquire all the user names and id numbers
        $decoded = json_decode($response, true); // Parses response and allows user to see table of arrays
        
        $assignableUsers['0'] = "None";
            for ($i = 0; $i < count($decoded['team']['members']); $i++) { // run through each member in the team
                if (strpos($_POST['assigneeIDString'], strval($decoded['team']['members'][$i]['user']['id'])) !== false) { // if user is not assigned, add to assignableUsers array
                }
                else {
                    $assignableUsers[$decoded['team']['members'][$i]['user']['id']] = $decoded['team']['members'][$i]['user']['username']; // set the key as the ID and the element as the username
                }
            }

        $data['assignableUsers'] = $assignableUsers;
        $data['taskID'] = $_POST['taskID'];    
        $this->load->vars($data);
        
        $template = 'clickup/addAssigneeModal';
        $this->load->view($template);
    }    
    
/*
* addAssignee() function
* This function calls ClickUp to add a user to a task
* @Author: Anthony Natale
* @Date: 11/15/2018
*/
    
    public function addAssignee() {
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        $addAssignee = '"add":[ ' . $_POST['assigneeID'] . ' ]';
        $json = '{ "assignees": { ' . $addAssignee . ' } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
/*
* statusModal() function
* Opens up the status change modal window
* @Author: Anthony Natale
* @Date: 1/14/2018
*/
    public function statusModal() { 
        $template = 'clickup/statusModal';
        $data['taskStatusList'] = $_POST['taskStatusList'];
        $data['taskStatus'] = $_POST['taskStatus'];
        $data['taskID'] = $_POST['taskID'];
        $this->load->vars($data);
        $this->load->view($template); 
    }


/*
* statusChange() function
* Opens up the status change modal window
* @Author: Anthony Natale
* @Date: 1/14/2018
*/
    public function statusChange() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{
          "status": "'. $_POST['taskStatus'] . '"
        }');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);;    
    }
/*
* individualTaskDateChange() function
* This function makes the ClickUp API call to edit a due date
* @Author: Anthony Natale
* @Date: 2/6/2018
*/
    public function individualTaskDateChange() { 
        // Getting the authorization header
        $authorizationHeader = $this->buildHeader();
        
        $json = '{ "due_date": "' . strtotime($_POST['newDate']) . "000" . '" }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.clickup.com/api/v1/task/" . $_POST['taskID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          $authorizationHeader,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        var_dump($response);
        }
} 
