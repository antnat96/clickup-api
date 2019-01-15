/*
* authTest() function
* @Author: Anthony Natale
* @Date: 9/11/2018
* @Purpose: Checks the API key to see if ClickUp gives a good response and directs the user to the tasks view or to the fixKey view
*/

function authTest() {          
    var url = pickURL('/clickup/getKey');
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val()},
        success: function(result) {
            if (result.includes("bad")) {
                fixKey();
            }
            else if (result.includes("good")) {
                displayTasks();
            }
            else {
                clickUpErrorModal(result);
            }
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* displayTasks() function
* @Author: Anthony Natale
* @Date: 9/11/2018
* @Purpose: Calls the tasks controller function and then view
*/

function displayTasks() {           
    var url = pickURL('/clickup/tasks');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val()},
        success: function(result) { 
            window.location.replace(url);
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* fixKey() function
* @Author: Anthony Natale
* @Date: 9/11/2018
* @Purpose: Calls the fixKey controller function and then the authTest view
*/

function fixKey() {           
    var url = pickURL('/clickup/fixKey');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val()},
        success: function(result) { 
            window.location.replace(url);
            $('#preloading').hide();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* clickUpAPIKey() function
* @Author: Anthony Natale
* @Date: 6/20/2018
* @Purpose: Called from introduction on click of settings wheel. Generates modal popup window that 
* allows user to enter ClickUp API Token to be saved to FileMaker.
*/

function clickUpAPIKey() {
    var url = pickURL('/clickup/editAPIKey');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val()},
        success: function(result) {
            if(result != ''){
                $('#api-key-modal').html(result);
                $('#api-key-modal').openModal();
            }
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
}

/*
* submitAPIKey() function
* @Author: Anthony Natale
* @Date: 6/20/2018
* @Purpose: Called from editKey.php view, calls submitAPIKey function in clickup.php to update the ClickUpAPIKey field in filemaker for that user
*/

function submitAPIKey(ClickUpAPIKey) {
    var url = pickURL('/clickup/submitAPIKey');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(),ClickUpAPIKey:$('#ClickUpAPIKey').val()},
        success: function(result) {
                $('#api-key-modal').closeModal();
                authTest();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
}

/*
* newTaskModal() function
* This function provides support for the "New Task" button in tasks.php view
* Calls a clickup.php controller function to open a modal window where user can input their new task info
* @Author: Anthony Natale
* @Date: 7/2/2018
*/

function newTaskModal() {           
    var url = pickURL('/clickup/newTaskModal');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val()},
        success: function(result) {
            $('#new-task-modal').html(result);
            $('#new-task-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* createNewTask() function
* This function calls the createNewTask function in the clickUp php controller
* @Author: Anthony Natale
* @Date: 7/2/2018
*/

function newTask(newTaskName, newTaskDesc, newTaskAssignee, newTaskStatus, newTaskPriority, newTaskDueDate, newTaskListID) {
    console.log(newTaskName.value + "  " + newTaskDesc.value + "  " + newTaskAssignee.value + "  " + newTaskStatus.value + "  " + newTaskPriority.value + "  " + newTaskDueDate.value + "  " + newTaskListID.value);
    var url = pickURL('/clickup/newTask');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show();},
        complete: function() { },
        type: 'POST',
        data:  {newTaskName:newTaskName.value, newTaskDesc:newTaskDesc.value, newTaskAssignee:newTaskAssignee.value, newTaskStatus:newTaskStatus.value, newTaskPriority:newTaskPriority.value, newTaskDueDate:newTaskDueDate.value, newTaskListID:newTaskListID.value},
        success: function(result) {
            console.log(result);
            $('#new-task-modal').closeModal();
            if (false) { // Catch the bad clickup API call
                clickUpErrorModal(result);
            }
            else {
                displayTasks();
            }      
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* deleteTask() function
* This function calls the deleteTask function in the clickUp php controller
* Note: this feature currently does not have ClickUp API support. This feature is planned to be added to their API.
* @Author: Anthony Natale
* @Date: 7/2/2018
*/

function deleteTask() {
    var url = pickURL('/clickup/deleteTask');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {PrimaryEmail: $('#PrimaryEmail').val()},
        success: function(result) {
            $('#delete-task-modal').html(result);
            $('#delete-task-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* statusModal() function
* Opens the status modal
* @Author: Anthony Natale
* @Date: 1/14/2018
*/

function statusModal(taskID, taskStatus, taskStatusList) {
    var url = pickURL('/clickup/statusModal');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {taskID: taskID,taskStatus: taskStatus, taskStatusList:taskStatusList},
        success: function(result) {
            $('#status-change-modal').html(result);
            $('#status-change-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* statusChange() function
* Changes the status
* @Author: Anthony Natale
* @Date: 1/14/2018
*/

function statusChange(taskID, taskStatus) {
    var url = pickURL('/clickup/statusChange');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {taskID: taskID,taskStatus: taskStatus},
        success: function(result) {
            $('#status-change-modal').closeModal();
            displayTasks();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};
/*
* editTaskModal() function
* This function calls the editTaskModal function in the clickUp php controller
* @Author: Anthony Natale
* @Date: 7/2/2018
*/

function editTaskModal(taskID, taskName, taskDesc, taskStatus, taskPriority, taskDueDate, assigneeIDString, teamID, taskStatusList) {
    var url = pickURL('/clickup/editTaskModal');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {taskID: taskID, taskName: taskName, taskDesc: taskDesc, taskStatus: taskStatus, taskPriority: taskPriority, taskDueDate: taskDueDate, assigneeIDString: assigneeIDString, teamID:teamID, taskStatusList:taskStatusList},
        success: function(result) {
            $('#edit-task-modal').html(result);
            $('#edit-task-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* editTask() function
* Calls the editTask php controller function and passes all the task data
* @Author: Anthony Natale
* @Date: 7/2/2018
*/

function editTask(taskID, taskName, taskDesc, addTaskAssignee, removeTaskAssignee, taskStatus, taskPriority, taskDueDate) {
    var url = pickURL('/clickup/editTask');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show();},
        complete: function() { },
        type: 'POST',
        data:  {taskID: taskID, taskName: taskName.value, taskDesc: taskDesc.value, addTaskAssignee: addTaskAssignee.value, removeTaskAssignee: removeTaskAssignee.value, taskStatus: taskStatus.value, taskPriority: taskPriority.value, taskDueDate: taskDueDate.value},
        success: function(result) {
            //console.log(result);
            $('#edit-task-modal').closeModal();
            if (result.includes('err')) {
                clickUpErrorModal(result);
            }
            else {
               displayTasks();
            }
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* clickUpErrorModal() function
* Shows the error modal
* @Author: Anthony Natale
* @Date: 10/12/2018
*/

function clickUpErrorModal(errorMessage) {
    var url = pickURL('/clickup/clickUpErrorModal');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show();},
        complete: function() { $('#preloading').hide();},
        type: 'POST',
        data:  {errorMessage: errorMessage},
        success: function(result) {
            $('#clickup-error-modal').html(result);
            $('#clickup-error-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* markTaskComplete() function
* Makes an API call to mark the task as closed and then reloads the page
* @Author: Anthony Natale
* @Date: 9/24/2018
*/

function markTaskComplete(taskID) {
    var url = pickURL('/clickup/markTaskComplete');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), taskID: taskID},
        success: function(result) {
            displayTasks();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* upgradeStatus() function
* Makes an API call to move the status of the task to the next status
* @Author: Anthony Natale
* @Date: 9/17/2018
*/

function upgradeStatus(taskID, upgradeTo) {
    var url = pickURL('/clickup/upgradeStatus');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), taskID: taskID, upgradeTo:upgradeTo},
        success: function(result) {
            displayTasks();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* downgradeStatus() function
* Makes an API call to move the status of the task to the previous status
* @Author: Anthony Natale
* @Date: 9/24/2018
*/

function downgradeStatus(taskID, downgradeTo) {
    var url = pickURL('/clickup/downgradeStatus');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), taskID: taskID,downgradeTo:downgradeTo},
        success: function(result) {
            displayTasks();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* closeModal() function
* @Author: Anthony Natale
* @Date: 10/29/2018
* @Purpose: The one modal closing function to rule them all
* @Parameters: modal ID passed in from the view
*/

function closeModal(modalID) {
    $("#"+modalID).closeModal();
}

/*
* pickURL() function
* @Author: Anthony Natale
* @Date: 11/13//2018
* @Purpose: The one AJAX URL building function to rule them all
* @Parameters: destination function location passed in from js function
*/

function pickURL(destination) {
        // hardcode url's fixed using hostname function
    var hostname = getHostName(window.location.href);
    var url;
    if(hostname.indexOf('bridge') != -1){
        url = destination;
    }
    else {
        if (window.location.href.indexOf("dev")) {
            var arr_url = window.location.href.split("/");
            var index = 0;
            for (var i = 0; i < arr_url.length; i++) {
                if (arr_url[i] === "dev") {
                    index = i;
                    break;
                }
            }
            url =  "/" +arr_url[index]+"/"+ arr_url[index+1] +'/bridge' + destination;
        }
        else {
            url =  destination;
        }
    }
    return url;
}

/*
* removeIndAssignee() function
* @Author: Anthony Natale
* @Date: 11/13//2018
* @Purpose: Calls controller function that makes API call to remove an individual user from the assignees list
* @Parameters: user id passed in from tasks.php, task id
*/

function removeIndAssignee(assigneeID, taskID) {
    var url = pickURL('/clickup/removeIndAssignee');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), assigneeID: assigneeID, taskID: taskID},
        success: function(result) {
            displayTasks();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* addAssigneeModal() function
* @Author: Anthony Natale
* @Date: 11/13//2018
* @Purpose: Generates the add assignee modal window so an individual assignee can be added to a task
* @Parameters: task id, string of all assignees id numbers, string of all assignees names
*/

function addAssigneeModal(taskID, assigneeIDString, teamID) {
    var url = pickURL('/clickup/addAssigneeModal');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() { $('#preloading').hide(); },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), taskID:taskID, assigneeIDString:assigneeIDString, teamID:teamID},
        success: function(result) {
            $('#add-assignee-modal').html(result);
            $('#add-assignee-modal').openModal();
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* removeIndAssignee() function
* @Author: Anthony Natale
* @Date: 11/13//2018
* @Purpose: Calls controller function that makes API call to add an individual user to the assignees list
* @Parameters: task id number, assignee id number to add to task
*/

function addAssignee(taskID, assigneeID) {
    var url = pickURL('/clickup/addAssignee');        
    $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {PrimaryEmail:$('#PrimaryEmail').val(), taskID:taskID, assigneeID: assigneeID.value},
        success: function(result) {
            $('#add-assignee-modal').closeModal();
            if (result.includes('err')) {
                clickUpErrorModal(result);
            }
            else {
                displayTasks();
            }
        },
        error: function(e) {
            //called when there is an error
            console.log(e.message);
        }
    });
};

/*
* populateLists() function
* @Author: Anthony Natale
* @Date: 12/3//2018
* @Purpose: Gets all the lists for the newTask.php document
* @Parameters: space id number
*/

function populateLists(spaceID) {
    // Empty the list span
    if ($('#listSelectDropDown').is(":parent")) {
        $('#listSelectDropDown').html(placeholder);
    }
    
    // Variables to pass to the controller
    var projNum = $('#projectSelect').prop('selectedIndex') - 1; // Use index - 1 because arrays start at 0, catch -1 in controller
    var projID = $('#projectSelect').prop('value');
    
    // Ajax to controller to get lists for this project
    if (projID != 0) { // if the user selected something other than "select"
        var url = pickURL('/clickup/getLists'); // Gets URL to controller function       
        $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {spaceID:spaceID, projNum:projNum},
        success: function(result) {
            // Replace the lists span with the dropdown of lists
            $('#listSelectDropDown').html(result);
            
            // Make it alive with materialize
            $( "#listSelect" ).material_select();
            
            // If there's one list
            if ($("#listSelect option").size() == 1) {
                var listID = $("#listSelect option").val();
                $('#listID').val(listID); // where the rubber meets the list ID
            }
            validateNewTask();
            $('#preloading').hide();
        }                
        }); 
    }    
            

}

/*
* populateProjects() function
* @Author: Anthony Natale
* @Date: 12/3/2018
* @Purpose: Gets all the projects for the newTask.php document
* @Parameters:
*/

function populateProjects() {    
    // Ensure the later dropdowns are empty
    if ($('#projectSelectDropDown').is(":parent")) {
        $('#projectSelectDropDown').html(placeholder);
    }
    if ($('#listSelectDropDown').is(":parent")) {
        $('#listSelectDropDown').html(placeholder);
    }
    
    // Variable to pass to the controller
    var spaceID = $('#spaceSelect option').prop("value"); // Get the space ID
    
    // Ajax to controller to get the projects for this space
    if (spaceID != 0) {                
        var url = pickURL('/clickup/getProjects'); // Gets URL to controller function       
        $.ajax({
        url: url,
        beforeSend: function() { $('#preloading').show(); },
        complete: function() {  },
        type: 'POST',
        data:  {spaceID:spaceID},
        success: function(result) {
            // Replace the projects span with the dropdown of projects
            $('#projectSelectDropDown').html(result);
            
            // Make it alive with materialize
            $( "#projectSelect" ).material_select();
            
            // if there's one project
            if ($("#projectSelect option").size() == 1) { // load the lists dropdown into the lists span
                populateLists(spaceID);
            }
            else {
                $('#preloading').hide();
            }
        }
        });
    }         
}

// functions to clear the drop down spans in newTask.php when needed
function clearSpaces(placeholder) {
    if ($('#spaceSelectDropDown').is(":parent")) {
        $('#spaceSelectDropDown').html(placeholder);
    }
}   

function clearProjects(placeholder) {
    if ($('#projectSelectDropDown').is(":parent")) {
        $('#projectSelectDropDown').html(placeholder);
    }
}  

function clearLists(placeholder) {
    if ($('#listSelectDropDown').is(":parent")) {
        $('#listSelectDropDown').html(placeholder);
    }
} 

function validateNewTask() {
    if ($('#newTaskName').val().trim().length > 0 && $("#listID").val().length > 3) {
        $("#submitNewTaskButton").prop("disabled", false);
        $("#submitNewTaskButton").removeClass("disable-submit");
    }
    else {
        $("#submitNewTaskButton").prop("disabled", true);
        $("#submitNewTaskButton").addClass("disable-submit");    }
}

/*
* getCheckBoxValues() function
* @Author: Anthony Natale
* @Date: 12/21/2018
* @Purpose: Gathers the values of checked checkboxes in editTask.php view for the editTask() function
*/

function getCheckBoxValues() {
    var toAdd = "";
    $.each($("input[name='assignAssignee']:checked"), function(){            
        toAdd = (toAdd  + " " + $(this).val());
    });
        $("#addTaskAssignees").val(toAdd);        
    var toRem = "";
    $.each($("input[name='removeAssignee']:checked"), function(){            
        toRem = (toRem  + " " + $(this).val());
    });
        $("#removeTaskAssignees").val(toRem);        
};
