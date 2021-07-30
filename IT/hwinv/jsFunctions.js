/*
    Frontend Javascript for the IT Inventory Tool
*/

/*
 * operation: create HTML table of relevant hardware inventory items
 * arguments: array: JSON array of a MySQL table
 *            src: string, name of table used as its id
 * return: an HTML table
 */
function makeTable (array, src)
{   
    var table  = $('<table border="1" id="'+ src +'" class="table table-bordered table-condensed">');
    var header = '<thead><tr>';
    var first  = 0;
    var heads  = [];
    
    for (var top in array[0])
    {
        // insert addRow icon in the first table head cell
        if(first === 0)
        {
            header+= "<th>" + '<span class="addRow"><i id="addRow" class="fa fa-plus-square-o" style="font-size:20px;"></i></span>' + top + "</th>";
            first++;
            heads.push(top);
            continue;
        }
        // if building the vendors table, insert the addVendor icon in the vendors table head cell
        if(src === "models" && top === "vendor")
        {
            header+= "<th>" + '<span class="addVendor"><i id="addVendor" class="fa fa-plus-square-o" style="font-size:20px;"></i></span>' + top + "</th>";
            heads.push(top);
            continue;
        }
        header += "<th>" + top + "</th>";
        heads.push(top);
    }
    header += "</tr></thead>";
    $(header).appendTo(table);
    
    // use tbody so filtering can ignore the table header
    var tbody = "<tbody>";
    $.each(array, function (index, value) 
    {
        var TableRow = '<tr>';
        var col = 0;
        var vals = [];
        $.each(value, function (key, val) 
        {
            // add deleteRow icon to the first cell in the row
            if(col === 0)
            {
                TableRow += '<td id="' + heads[col] + '"><span class="deleteRow"><i id="deleteRow" class="fa fa-window-close" style="font-size:20px;"></i></span>' + val + '</td>';
                col++;
            }
            else
            {
                TableRow += '<td id="' + heads[col] + '">' + val + '</td>';
                vals.push(val);
                col++;
            }
        });
        TableRow += "</tr>";
        // multivalue cells will separate their values with line breaks instead of commas
        TableRow = TableRow.replace(/,/g, "<br>");
        tbody += TableRow;
    });
    tbody += "</tbody>";
    $(table).append(tbody);
    return ($(table));
}

/*
 * operation: convert an html table row directly into an array
 * arguments: an html table row           
 * return: a javascript array of the row 
 */
function rowToArray(row)
{
    var tds   = row.find("td");
    var arRow = [];
    $.each(tds, function(){
        arRow.push($(this).text());
    });
    return arRow;
}

/*
 * operation: determine if the row's primary key operates on itself or is used to locate the proper row
 * arguments: task: a getEditFunction object that contains operation and modal instructions for the cell
 *            current: a string of the data in the selected cell
 *            newData: a string of updated data
 *            row: an array of the current table row
 *            table: the table from which the operation was initiated
 */
function saveCell(task, current, newData, row, table, field)
{
    console.log(task + " " + current + " " + newData + " " + row + " " + table + " " + field);
    var pk = row[0];
    var args = [];
    
    if(task.selfOp)
    {
        args[0] = current;
    }
    else
    {
        args[0]  = pk;     
    }
    args[1]  = newData;
    
    if(task.op == "updateNote")
    {
        //old, new, hostname
        args[0] = current;
        args[1] = newData;
        args[2] = field;
    }
    
    console.log(task.op + ", arguments: " + args + ", table: " + table);
    send(task.op, args, "#ops_results", table);
}

/*
 * operation: set a row's assigned host or user to null, freeing it
 * arguments: pk: string of the primary key of a row
 *            table: string of the current table
 */
function freePK(pk, table)
{
    var op = "";
    var args = [pk];
    
    switch(table)
    {
        case "hosts":
            op = "freeHost";
            break;
        case "windowskeys":
            op = "freeKey";
            break;
        case "macaddresses":
            op = "freeMac";
            break;
        default:
            break;
    }
    console.log("op: " + op +  " pk: " + args[0]);
    send(op, args, "#ops_results", table);
    //hide editcell modal 
    $("#editCell").css("display", "none");
}

/*
 * operation: send a function to the backend with its arguments
 * arguments: func: a string of a function name
 *            args: a javascript array of arguments
 *            resultDiv: string of the div ID in which the result should be displayed
 *            table: the table from which the function was called
 */
function send(func, args, resultDiv, table)
{
    console.log(func + " " + args + " " + resultDiv + " " + table);
    $.ajax({
        type: "POST",
        url: "functions.php", 
        data: {function:func, args:args},
        success: function(Data){
            console.log('operation returned');
            $(resultDiv).html('');
            $(resultDiv).html(Data);
            document.getElementById("ops").style.display = "block";
            if(table != "none")
            {
                refreshTables(table);
            }      
        },
        error: function(error){
            console.log('operation failed');
            console.error(error);
        }
    }); 
}

/*
 * operation: parse the select generated for a given cell
 * arguments: task: a getEditFunctionObject that contains operation and modal instructions for a cell
 *            row: a javascript array of the row of the selected cell
 *            current: a string of the current value of the selected cell
 *            table: a string of the currently selected table
 */
function saveSelect(task, row, current, table)
{ 
    selectedOption = $('#cellSelect>option:selected').text();
    optionID       = $("#cellSelect").val();
    var pk         = row[0];
    var args       = [];
    
    args[0] = pk;
    args[1] = optionID;
    
    console.log(pk + "::->" + optionID);
    
    // if what was selected differs from the current value of the cell
    if(current !== selectedOption)
    {        
        send(task.op, args, "#ops_results", table);
        $("#cellSelect").html('');
    }
    else
    {
        console.log('no changes');
    }
    $("#editCell").css("display", "none");
    
}

/*
 * operation: determine the operation, modal, and properties appropriate to the selected cell
 * arguments: table: the currently selected table
 *            field: the column of the currently selected table
 *            tables: a JSON array of all tables in the database
 * return:    task: an object containing cell instructions and properties
 */
function getEditFunction(table, field, tables)
{
    // define defaults, set according to cell type
    var task = {
        op: "",          // backend operation 
        select: false,   // whether the cell is inline or select edit
        editable: true,  // whether the cell is editable
        modal: "",       // the modal that edits the cell
        freeable: false, // whether the cell contains a primary key that can have its asset freed
        selfOp: false,   // whether the cell uses itself as an argument (i.e. update)
        tempTable: false // whether editing this cell's modal has a special minitable 
    };
   
    // all table cells across all 4 tables are represented here
    switch (table)
    {
        case "hosts":
            switch(field){
                case "hostname":
                    task.op = "updateHostname";
                    task.selfOp = true;
                    break;
                case "PIN":
                    task.op = "updateAdminPIN";
                    break;
                case "vendor":
                    task.editable = false;
                    task.select = true;
                    task.modal = "vendorModelModal";
                    break;
                case "model":
                    task.op = "updateHostModel";
                    task.select = true;
                    task.modal  = "vendorModelModal";
                    break;
                case "type":
                    task.op = "updateTypeHost";
                    task.select = makeTypeSelect(tables.hosttype);
                    task.modal = "editCell";
                    break;
                case "tag":
                    task.op = "updatePTHost";
                    break;
                case "STSN":
                    task.op = "updateSTSNHost";
                    break;
                case "MACs":
                    task.editable = false;
                    break;
                case "deployed":
                    task.op = "upDate";
                    break;
                case "user":
                    task.editable = false;
                    break;
                case "notes":
                    task.op = "updateNote";
                    task.modal = "noteModal";
                    task.tempTable = true;
                    task.editable = false;
                    break;
                default:
                    break;
            }
            break;
        case "windowskeys":
            switch(field){
                case "winKey":
                    task.op = "updateKey";
                    task.selfOp = true;
                    break;
                case "version":
                    task.op = "updateKeyType";
                    task.select = makeKeyVerSelect(tables.windowsversions);
                    task.modal = "editCell";
                    break;
                case "host":
                    task.op = "updateKeyHost";
                    task.select = makeHostSelect(tables.hosts);
                    task.modal = "editCell";
                    task.freeable = true;
                    break;
                case "sticker":
                    task.op = "updateKeySticker";
                    task.select = makeHostSelect(tables.hosts);
                    task.modal = "editCell";
                    task.freeable = true;
                    break;
                case "free":
                    task.editable = false;
                    break;
                case "upgrade":
                    task.editable = false;
                    break;
                case "status":
                    task.op = "updateKeyStatus";
                    task.select = makeStatusSelect();
                    task.modal = "editCell";
                    break;
                default:
                    break;
            }
            break;
        case "macaddresses":
            switch(field){
                case "macAddress":
                    task.op = "updateMac";
                    task.selfOp = true;
                    break;
                case "hostname":
                    task.op = "updateMacHost";
                    task.select = makeHostSelect(tables.hosts);
                    task.modal = "editCell";
                    task.freeable = true;
                    break;
                default:
                    break;
            }
            break;
        case "models":
            switch(field){
                case "model":
                    task.op = "updateModel";
                    task.selfOp = true;
                    break;
                case "vendor":
                    task.op = "updateModelVendor";
                    task.modal = "editCell";
                    task.select = makeVendorSelect(tables.vendors);
                    task.editable = false;
                    break;
            }
            break;
        case "hardware":
            switch(field){
                case "hostname":
                    task.editable = false;
                    break;
                case "motherboard":
                    task.op = "updateMotherboard";
                    break;
                case "graphics":
                    task.op = "updateGraphics";
                    break;
                case "CPU":
                    task.op = "updateCPU";
                    break;
                case "CPU_specs":
                    task.op = "updateCPU_specs";
                    break;
                case "RAM_total":
                    task.op = "updateRAM_total";
                    break;
                case "RAM_specs":
                    task.op = "updateRAM_specs";
                    break;
                case "storage-primary":
                    task.op = "updateStoragePrimary";
                    break;
                case "storage-additional":
                    task.op = "updateStorageAdditional";
                    break;
                case "chipset":
                    task.op = "updateChipset";
                    break;
                case "BIOS_ver":
                    task.op = "updateBIOS_ver";
                    break;
            }
            break;
        case "hostNotes":
            task.op = "updateNote";
            break;
        case "miniVendors":
            task.op = "updateVendor";
            task.selfOp = true;
            break;
        default:
            break;
    }    
    return task;
}

/*
 * operation: update the vendor and model of a host; model depends on vendor selection
 * arguments: row: the table row to which the selected cell belongs
 *            current: the data in the currently selected cell  
 */
function vendorModelUpdate(row, current)
{
    pk = row[0];
    var model    = $("#models>option:selected").text();
    var modelID  = $("#models").val();
    var args     = [];
    args[0]      = pk;
    args[1]      = modelID;
    console.log(current + " VS " + model);
    console.log(pk + "::" + modelID);
    
    // if the selected option differs from the cell's current value
    if(model !== current)
    {
        send("updateHostModel", args, "#ops_results", "hosts");
    }
    else
    {
        console.log('no changes');
    }
    
    $("#vendorModelModal").css("display", "none");
}

/*
 * operation: display a modal (all modals hidden by default)
 * arguments: modal: the name of the modal to display
 */
function showModal(modal)
{
    document.getElementById(modal).style.display = "block";
    console.log("show " + modal);
}

/*
 * operation: hide a modal
 * arguments: modal: the modal to hide
 */
function hideModal(modal)
{
    document.getElementById(modal).style.display = "none";
    console.log("hide " + modal);
}

/*
 * operation: make an html select of all available vendors
 * arguments: vendors: a JSON array of the vendors table from the database
 * return: an html select of all vendors
 */
function makeVendorSelect (vendors)
{
    var options = '';
    for(var i = 0; i < vendors.length; i++)
    {
        options += '<option value="' + vendors[i].vendorID + '">' + vendors[i].vendor + '</option>';
    }
    return options;
}

/*
 * operation: make an html select of all users in LDAP (soon to be deprecated)
 * arguments: users: a JSON array of all the users in the database
 * return: an html select of all users
 */
function makeUserSelect (users)
{
    var options = '';
    for(var i = 0; i < users.length; i++)
    {
        options += '<option value="' + users[i].ldapUID + '">' + users[i].username + '</option>';
    }
    return options;
}

/*
 * operation: make an html select of all the models belonging to a given vendor
 * arguments: models: a JSON array of all the models in the database
 * return: an html select of all the models belonging to the vendor currently selected
 */
function makeModelSelect (models)
{ 
    // get the currently selected vendor
    var vendorID = $("#vendors").val();
    console.log('makeModelSelect called with vendorID ' + vendorID);
    var options = '';
    for(var i = 0; i < models.length; i++)
    {
        // only add models to the list that have the selected vendorID
        if(models[i].vendorID === vendorID)
        {
            options += '<option value="' + models[i].modelID + '">' + models[i].model + '</option>';
        }
    }
    $("#models").html(options);
}

/*
 * operation: make an html select of all hosts 
 * arguments: hosts: a JSON array of all the hosts in the database
 * return: an html select containing all the hosts in the database
 */
function makeHostSelect(hosts)
{
    var options = '';
    for (var i = 0; i < hosts.length; i++)
    {    
        options += '<option value="' + hosts[i].hostID + '">' + hosts[i].hostname + '</option>';
    }
    return options;
}

/*
 * operation: make an html select of the various windows versions used at QSAI
 * arguments: versions: a JSON array of all the versions of windows in the database
 * return: an html select of all the windows versions in the database
 */
function makeKeyVerSelect(versions)
{
    var options = '';
    for(var i = 0; i < versions.length; i++)
    {
        options += '<option value="' + versions[i].winVerID + '">' + versions[i].windowsVersion + '</option>';
    }
    return options;
}

/*
 * operation: make an html select of the different host types
 * arguments: hosttype: a JSON array of the hosttype table in the database
 * return: an html select containing all the host types in the database
 */
function makeTypeSelect(hosttype)
{
    var options = '';
    for (var i = 0; i < hosttype.length; i++)
    {
        options += '<option value="' + hosttype[i].typeID + '">' + hosttype[i].hostType + '</option>';
    }
    return options;
}

/*
 * operation: make a select containing verified and failed for key status
 * return: an html select with verified and failed as options
 */
function makeStatusSelect()
{
    var options = '';
    options += '<option value="0">verified</option>';
    options += '<option value="1">failed</option>';
    return options;
}

/*
 * operation: add a new hostname to the database
 */
function addHost ()
{
    // get the hostname from the addhostsmodal
    var hostname = $("#hostname").val();
    var args = [];
    var op = "addHost";
    args[0] = hostname; 
    
    $.ajax({
        type: "POST",
        url: "functions.php", 
        data: {function:op, args:args},
        success: function(Data){
            console.log('operation returned');
            $("#ops_results").html('');
            $("#ops_results").html(Data);
            document.getElementById("ops").style.display = "block";
            hideModal("addhostsModal");
            refreshTables("hosts");
        },
        error: function(error){
            console.log('operation failed');
            console.error(error);
        }
    });
}

/*
 * operation: add a new MAC address to the database
 */
function addMac()
{
    // get the new MAC and its host from the addmac modal
    var macInput = $("#macInput").val();
    var macHost  = $("#macHost").val();
    var args = [];
    var op = "addMac";
    args[0] = macInput;
    args[1] = macHost;
    
    $.ajax({
        type: "POST",
        url: "functions.php", 
        data: {function:op, args:args},
        success: function(Data){
            console.log('operation returned');
            $("#ops_results").html('');
            $("#ops_results").append(Data);
            document.getElementById("ops").style.display = "block";
            hideModal("addmacaddressesModal");
            refreshTables("macaddresses");
        },
        error: function(error){
            console.log('operation failed');
            console.error(error);
        }
    });
}

/*
 * operation: add a new Windows key to the database
 */
function addKey()
{
    // get the Windows key and selected key version from the addkey modal
    var keyInput = $("#keyInput").val();
    var keyVer   = $("#keyVer").val();
    var args = [];
    args[0] = keyInput;
    args[2] = keyVer;
     
    $.ajax({
        type: "POST",
        url: "functions.php", 
        data: {function:"addKey", args:args},
        success: function(Data){
            console.log('operation returned');
            $("#ops_results").html('');
            $("#ops_results").append(Data);
            document.getElementById("ops").style.display = "block";
            hideModal("addwindowskeysModal");
            refreshTables("windowskeys");
        },
        error: function(error){
            console.log('operation failed');
            console.error(error);
        }
    }); 
}

/*
 * operation: add a new vendor to the database
 */
function addVendor()
{
    // get the new vendor from the addvendor modal
    var newVendor = $("#newVendor").val();
    var args = [];
    args[0] = newVendor;
    
    send("addVendor", args, "#ops_results");
    hideModal("addmodelModal");
    refreshTables("models");
}

/*
 * operation: add a new model from the database
 */
function addModel()
{ 
    // get the new model and its vendor from the addmodel modal
    var vendor   = $("#vendors_new").val();
    var newModel = $("#newModel").val();
    var args = [];
    args[0] = vendor;
    args[1] = newModel; 
    send("addModel", args, "#ops_results");
    hideModal("addmodelsModal");
    refreshTables("models");
}

/*
 * operation: add new hardware data for a host in the database
 * 
 */
function addHardware()
{
    var select = document.getElementById("hardware");
    var hostname = select.options[select.selectedIndex].text;
    var args = [];
    args[0] = hostname;
    send("addHardware", args, "#ops_results");
    hideModal("addhardwareModal");
    refreshTables("hardware");   
}

/*
 * operation: properly escape HTML entered into the note textarea
 * arguments: text: a string to be sanitized
 * return: a string that has all its html escaped
 */
function escapeHTML (text)
{
    // put the text in question in a new div
    var div = document.createElement('div');
    // set its text to the input string
    div.innerText = text;
    // return the innerHTML of the new div, it will be escaped
    return div.innerHTML;
}

/*
 * operation: add a new note about a host to the database
 * arguments: host: a hostname string
 */
function addNote(host)
{
    // get the note from the textarea of the addnote modal
    var note = $("#newNote").val();
    // all notes must be sanitized
    note = escapeHTML(note);
    
    var args = [];
    args[0]  = host;
    args[1]  = note;

    console.log(host + " note: " + note);

    $.ajax({
        type: "POST",
        url: "functions.php",
        data: {function:"addNote", args:args},
        success: function(Data){
            $("#ops_results").html('');
            $("#ops_results").html(Data);
            document.getElementById("ops").style.display = "block";
            $("#newNote").val('');
            var dbData = refreshTables("hosts");
            makeNoteTable(dbData.tables.note, dbData.tables.hosts, host);
        },
        error: function(error){
            console.log('failed to add note');
            console.error(error);
        }
    });
}

/*
 * operation: create a minitable of all the notes belonging to a host
 * arguments: notes: a JSON array of all the notes in the database
 *            hosts: a JSON array of all the hosts in the database
 *            hostname: a hostname string
 */
function makeNoteTable(notes, hosts, hostname)
{
    // array for all a host's notes, if any
    var hostNotes = [];
    // get the hostID associated with the hostname
    var hostID = getHostID(hosts, hostname);
    
    // get all the notes belonging to that hostID
    for(var i = 0; i < notes.length; i++)
    {
        if(notes[i].hostID === hostID)
        {
            hostNotes.push(notes[i]["text-note"]);
        }
    }
    
    // create the notes mini table for hostname
    var noteTable  = $('<table border="1" id="hostNotes" class="table table-bordered table-condensed">');
    header = "<tr><th>" + hostname + "'s notes" +  "</th></tr>";
    $(header).appendTo(noteTable);
    
    for(var i = 0; i < hostNotes.length; i++)
    {
        var TableRow = '<tr>';
        // use the hostname as the td ID for ease of operations
        // add a delete icon to the note's cell
        TableRow += '<td id="' + hostname + '"><span class="deleteNote"><i id="deleteNote" class="fa fa-window-close" style="font-size:20px;"></i></span>' + hostNotes[i] + '</td>';
        TableRow += "</tr>";
        $(noteTable).append(TableRow);
    }
    
    // put the minitable in the note modal
    $("#hostNotes").html(noteTable);
}

/*
 * operation: create a minitable containing all the vendors in the database
 * arguments: vendors: a JSON array of all the vendors in the database
 */
function makeVendorTable(vendors)
{
    var miniVendors = $('<table border="1" id="miniVendors" class="table table-bordered table-condensed">');
    header = "<tr><th>" + "Vendors" +  "</th></tr>";
    $(header).appendTo(miniVendors);
    
    for(var i = 0; i < vendors.length; i++)
    {
        var TableRow = '<tr>';
        // add a delete icon to the vendor's cell
        TableRow += '<td id="' + vendors[i].vendorID + '"><span class="deleteVendor"><i id="deleteVendor" class="fa fa-window-close" style="font-size:20px;"></i></span>' + vendors[i].vendor + '</td>';
        TableRow += "</tr>";
        $(miniVendors).append(TableRow);
    }
    
    // put the minitable in the addVendor modal
    $("#miniVendors").html(miniVendors);
}

/*
 * operation: remove a note from the database
 * arguments: note: the text of the note to remove
 *  hostname: the host to which the note belongs
 */
function deleteNote(note, hostname)
{
    var args = [];
    args[0] = note;
    args[1] = hostname;
    
    $.ajax({
        type: "POST",
        url: "functions.php",
        data: {function:"delNote", args:args},
        success: function(Data){
            $("#ops_results").html('');
            $("#ops_results").html(Data);
            document.getElementById("ops").style.display = "block";
            var dbData = refreshTables("hosts");
            makeNoteTable(dbData.tables.note, dbData.tables.hosts, host);
        },
        error: function(error){
            console.log('failed to delete note');
            console.error(error);
        }
    });
}

/*
 * operation: get the hostID associated with a given hostname
 * arguments: 
 * hosts: a JSON array of all the hosts in the database
 * hostname: the target hostname 
 * return: NULL if the host doesn't exist, the hostID of a hostname if it does
 */
function getHostID(hosts, hostname)
{
    for (var i = 0; i < hosts.length; i++)
    {
        if(hosts[i].hostname == hostname)
        {
            return hosts[i].hostID;
        }
    }
    return null;
}

/*
 * operation: get all the tables in the database, and use that data to populate the frontend
 * return: dbData: an object containing both JSON arrays of  database tables, join queries, and reconstructed inventory tables
 */
function getData()
{
    var dbData;
    var tables;
    var ht;
    var hostData;
    var keyData;
    var hostTable;
    var keysTable;
    var macsTable;
    var hwTable;
    
    $.ajax({
        type: "POST",
        url: "functions.php",
        async: false,
        data: {function:"tables"},
        success: function(Data){
            console.log('tables request success');
            //console.log(Data);
            var getAll = JSON.parse(Data);
            // tables, helper tables, and reconstructed inventory
            //tables   = getAll.result.tables;
            //ht       = getAll.result.ht;
            tables = getAllTables();
            ht = getHelperTables();
            //host/keyData = reconstructed spreadsheets w/ data from other tables
            hostData = constructHosts();
            keyData  = constructKeys();
            
            // create the HTML tables that represent database information
            hostTable = makeTable(hostData, "hosts");
            keysTable = makeTable(keyData, "windowskeys");
            macsTable = makeTable(ht["macHostname"], "macaddresses");
            vmTable   = makeTable(ht["VMmod"], "models");
            hwTable   = makeTable(tables["hardware"], "hardware");
            
            // put the raw tables and HTML tables in a dbData object for ease of reference and argument
            dbData = {tables: tables, ht: ht, hostdata: hostData, keydata: keyData, hosttable: hostTable, keystable: keysTable, macstable: macsTable, vmtable: vmTable, hwtable: hwTable};
            
            // populate selects with the latest data from the database
            $("#miniVendors").html(makeVendorTable(tables.vendors));
            $("#vendors").html(makeVendorSelect(tables.vendors));
            $("#vendors_new").html(makeVendorSelect(tables.vendors));
            $("#macHost").html(makeHostSelect(tables.hosts));
            $("#keyHost").html(makeHostSelect(tables.hosts));
            $("#keyVer").html(makeKeyVerSelect(tables.windowsversions));
            $("#hardware").html(makeHostSelect(tables.hosts));
        },
        error: function(error){
            console.log('tables get failure');
            console.error(error);
        }
    });
    
    return dbData;
}

function initData()
{
    var tables   = getAllTables();
    var ht       = getHelperTables();
    var hostData = constructHosts();
    var keyData  = constructKeys();
    var dbData = {tables: tables, ht: ht, hostdata: hostData, keydata: keyData};
    return dbData;
}

function populateSelects()
{
    var hosts = getTable("hosts");
    var windowsversions = getTable("windowsversions");
    var vendors = getTable("vendors");
    $("#miniVendors").html(makeVendorTable(vendors));
    $("#vendors").html(makeVendorSelect(vendors));
    $("#vendors_new").html(makeVendorSelect(vendors));
    $("#macHost").html(makeHostSelect(hosts));
    $("#keyHost").html(makeHostSelect(hosts));
    $("#keyVer").html(makeKeyVerSelect(windowsversions));
    $("#hardware").html(makeHostSelect(hosts));
}

function initTables()
{
    var dbData = initData();
    hostTable = makeTable(constructHosts(), "hosts");
    keysTable = makeTable(constructKeys(), "windowskeys");
    macsTable = makeTable(dbData.ht["macHostname"], "macaddresses");
    vmTable   = makeTable(dbData.ht["VMmod"], "models");
    hwTable   = makeTable(dbData.tables["hardware"], "hardware");
    var allTables = {hosttable:hostTable,keystable:keysTable,macstable:macsTable,vmtable:vmTable,hwtable:hwTable};
    var init = {data:dbData, inittables:allTables};
    return init;
}

function getAllTables()
{
    // 10 tables total so far
    var count = 10;
    var names = ["hardware", "hosts", "hosttype", "macaddresses", "models", "note", "status", "vendors", "windowskeys", "windowsversions"];
    var tables = {hardware:[], hosts:[], hosttype:[], macaddresses:[], models:[], note:[], status:[], vendors:[], windowskeys:[], windowsversions:[]};
    
    for(var i = 0; i < count; i++)
    {
        var table = getTable(names[i]);
        tables[names[i]] = table;
    }
    //console.log(tables);
    return tables;
}

function getTable (table)
{
    var get;
    var func = "getTable";
    var args = [];
    args[0] = table;
    
    // do it procedurally
    $.ajax({
        type: "POST",
        async: false,
        url: "functions.php", 
        data: {function:func, args:args},
        success: function(Data){
            //console.log(Data);
            var extract = JSON.parse(Data);
            get = extract.result;
        },
        error: function(error){
            console.log('failed to get table ' + table);
            console.error(error);
        }
    });
    //console.log(get);
    return get;
}

function getHelperTable(ht)
{
    var get;
    var func = "getHelperTable";
    var args = [];
    args[0] = ht;
    // do it procedurally
    $.ajax({
        type: "POST",
        async: false,
        url: "functions.php", 
        data: {function:func, args:args},
        success: function(Data){
            //console.log(Data);
            var extract = JSON.parse(Data);
            get = extract.result;
        },
        error: function(error){
            console.log('failed to get table ' + table);
            console.error(error);
        }
    });
    return get;
}

function getHelperTables()
{
    var count = 6;
    var names = ["macHostname", "vendorModels", "hostKeys", "hostStickers", "winVers", "VMmod"];
    var ht = {macHostname:[],vendorModels:[],hostKeys:[],hostStickers:[],winVers:[],VMmod:[]};
    
    for(var i = 0; i < count; i++)
    {
        var table = getHelperTable(names[i]);
        ht[names[i]] = table;
    }
    //console.log(ht);
    return ht;
}

function getAll()
{
    var tables = getAllTables();
    var ht     = getHelperTables();
    var dbData = {tables: tables, ht: ht};
    //console.log(dbData);
    return dbData;
}

function constructHosts()
{  
    var hosts        = getTable("hosts");
    var types        = getTable("hosttype");
    var notes        = getTable("note");
    var vendormodels = getHelperTable("vendorModels");
    var macHostname  = getHelperTable("macHostname");
    var count        = Object.keys(hosts).length;
    var hostData     = [];
    
    for(var i = 0; i < count; i++)
    {
        var hostRow = {hostname:"",PIN:"",vendor:"",model:"",type:"",tag:"",STSN:"",MACs:[],deployed:"",user:"",notes:[]};
        
        hostRow["hostname"] = hosts[i]["hostname"];
        
        if(hosts[i]["adminPIN"] !== null)
        {
            hostRow["PIN"] = hosts[i]["adminPIN"];
        }
        
        // extract the current host's vendor and model
        vendormodels.forEach(function(value, index){
            if(vendormodels[index]["modelID"] === hosts[i]["modelID"])
            {
                hostRow["vendor"] = vendormodels[index]["vendor"];
                hostRow["model"]  = vendormodels[index]["model"];
            }
        });
        
        // extract the current host's type
        types.forEach(function(value, index){
           if(types[index]["typeID"] === hosts[i]["typeID"])
           {
               hostRow["type"] = types[index]["hostType"];
           }
        });
        
        if(hosts[i]["propertyTag"] !== null)
        {
            hostRow["tag"] = hosts[i]["propertyTag"];
        }
        
        if(hosts[i]["serviceTag-serialNumber"] !== null)
        {
            hostRow["STSN"] = hosts[i]["serviceTag-serialNumber"]; 
        }
        
        // extract any and all MACs associated with the host
        var macNum = 0;
        macHostname.forEach(function(value, index){
            if(macHostname[index]["hostname"] === hosts[i]["hostname"])
            {
                hostRow["MACs"][macNum] = macHostname[index]["macAddress"];
                macNum++;
            }
        });
        
        if(hosts[i]["deploydate"] !== null)
        {
            hostRow["deployed"] = hosts[i]["deploydate"];
        }
        
        if(hosts[i]["ldapUID"] !== null)
        {
            hostRow["user"] = hosts[i]["ldapUID"]; 
        }
        
        var noteNum = 0;
        notes.forEach(function(value, index){
            if(hosts[i]["hostID"] === notes[index]["hostID"])
            {
               hostRow["notes"][noteNum] = notes[index]["text-note"]; 
               noteNum++;
            }
        });
        hostData.push(hostRow);
    }
    //console.log(hostData);
    return hostData;
}

function constructKeys()
{    
    var keys = getTable("windowskeys");
    var hostKeys = getHelperTable("hostKeys");
    var hostStickers = getHelperTable("hostStickers");
    var winVers = getHelperTable("winVers");
    var count = keys.length;
    var keyData = [];
    
    for(var i = 0; i < count; i++)
    {
        var keyRow = {winkey:"", version:"", host:"", sticker:"", free:"", upgrade:"", status:""};
        
        keyRow["winkey"] = keys[i]["windowsKey"];
        
        winVers.forEach(function(value, index){
            if(winVers[index]["windowsKey"] === keys[i]["windowsKey"])
            {
                keyRow["version"] = winVers[index]["windowsVersion"];
            }
        });
        
        if(keys[i]["hostID"] !== null)
        {
            hostKeys.forEach(function(value, index){
                if(hostKeys[index]["windowsKey"] === keys[i]["windowsKey"])
                {
                    keyRow["host"] = hostKeys[index]["hostname"];
                }
            });
        }
        
        if(keys[i]["hostIDSticker"] !== null)
        {
            hostStickers.forEach(function(value, index){
                if(hostStickers[index]["windowsKey"] === keys[i]["windowsKey"])
                {
                    keyRow["sticker"] = hostStickers[index]["hostname"];
                }
            });
        }
        
        if(keys[i]["hostID"] === null || keys[i]["hostID"] === 0)
        {
            keyRow["free"] = "License is available";
        }
        
        if(keys[i]["winVerID"] === 2 && keys[i]["hostID"] !== null)
        {
            keyRow["upgrade"] = "Windows 7=>10 Pro";
        }
        
        if(keys[i]["statusID"] === 0)
        {
            keyRow["status"] = "verified";
        }
        else
        {
            keyRow["status"] = "failed";
        }
        
        keyData.push(keyRow);
    }
    
    //console.log(keyData);
    return keyData;
}

/*
 * operation: reload the HTML tables after they've been changed or when they're switched to
 * arguments: table: the table to refresh
 * return: a dbData object containing all the most current database updates
 */
function refreshTables(table)
{
    var dbData = getData();
    //var refresh = getTable(table);
    switch(table)
    {
        case "hosts":
            $("#tableDisplay").html(dbData.hosttable);
            break;
        case "windowskeys":
            $("#tableDisplay").html(dbData.keystable);
            break;
        case "macaddresses":
            $("#tableDisplay").html(dbData.macstable);
            break;
        case "models":
            $("#tableDisplay").html(dbData.vmtable);
            break;
        case "hardware":
            $("#tableDisplay").html(dbData.hwtable);
            break;
        default:
            break;
    }
    filter();
    return dbData;
}

/*
 * operation: filter the currently displayed table via the provided query
 * arguments: none
 * return: none
 */
function filter()
{
    // get filter from text box
    var filter = $("#filter_box").val().toLowerCase();
    // put the filter in local storage for persistence
    localStorage.setItem("filter", filter);

    // get currently displayed table
    table = $("#tableDisplay").children().attr('id');
    table = "#" + table + " tbody tr";
    $(table).filter(function(){
        $(this).toggle($(this).text().toLowerCase().indexOf(filter) > -1);
    });
    console.log("current filter: " + filter + ".");
}

/*
 * operation: launch deletion confirm alert for vendors minitable
 * arguments: string: the vendor to delete
 * return: none
 */
function confirmDelVendor(vendor)
{
    var confirm = window.confirm("Are you sure you want to delete " + vendor + " from vendors?");
    if(confirm == true)
    {
        var args = [];
        args[0]  = vendor;
        send("delVendor", args, "#ops_results", "models");
    }     
}

/*
 * operation: display the row deletion modal
 * arguments: pk: the current row's primary key
 *            table: the current table
 */
function deleteRow(pk, table)
{
    document.getElementById("deleteConfirm").style.display = "block";
    $("#deleteTarget").html("Are you sure you want to delete " + pk + " from " + table + "?");
}

/*
 * operation: commit a deletion operation
 * arguments: pk: the current row's primary key
 *            table: the current table
 */
function confirmDelete(pk, table)
{
    // get the deletion operation appropriate to the selected cell+table
    var op    = getDeleteOp(table);
    var args  = [];
    args[0]   = pk;
    console.log("::deleting " + pk + " from " + table + " via " + op + "::");
    send(op, args, "#ops_results", table);
    // hide the dialog after the operation is sent
    document.getElementById("deleteConfirm").style.display = "none";
}

/*
 * operation: choose the deletion operation based on the current table
 * arguments: table: the current table
 * return: the appropriate delete operation to be sent to the database
 */
function getDeleteOp(table)
{
    var delOp = "";
    console.log("delete from table " + table);
    switch(table)
    {
        case "hosts":
            delOp = "delHost";
            break;
        case "windowskeys":
            delOp = "delKey";
            break;
        case "macaddresses":
            delOp = "delMac";
            break;
        case "models":
            delOp = "delModel";
            break;
        case "hardware":
            delOp = "delHardware";
            break;
        default:
            break;
    }
    console.log("operation: " + delOp);
    return delOp;
}

