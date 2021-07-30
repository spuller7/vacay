<?php
    session_start();
    if(!($_SESSION['loggedin']))
    {
        header('Location: login.php');
        exit;
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PC Inventory Tool</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="jsFunctions.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <a href="logout.php" title="Log Out">Log <?php echo $_SESSION['username']; ?> out</a>
        <a id="test" onclick="getHelperTables()">test getHelperTables</a>
        
        <div id ="filter">
            <p>
            Filter:
            <input type="text" id="filter_box">
            </p>
        </div>

        <div class="modal" id="addhostsModal" style="display: none;">
            <div class="modal-content" id="modal-content">
                <span class="close" onclick="hideModal('addhostsModal')">x</span>
                <h2>Add Host</h2>
                <p>Hostname: </p> <input type="text" id="hostname"><br>
                <a id="addHost" onclick="addHost()"><h2>Save</h2></a>
            </div>
        </div>
        
        <div class="modal" id="addmacaddressesModal" style="display: none;">
            <div class="modal-content" id="modal-content">
                <span class="close" onclick="hideModal('addmacaddressesModal')">x</span>
                <h2>Add MAC Address</h2>
                <p>MAC: </p><input type="text" id="macInput"><br>
                <p>Host: </p><select name="macHost" id="macHost"></select><br>
                <a id="addMac" onclick="addMac()"><h2>Save</h2></a>
            </div>
        </div>
        
        <div class="modal" id="addwindowskeysModal" style="display: none;">
            <div class="modal-content" id="modal-content">
                <span class="close" onclick="hideModal('addwindowskeysModal')">x</span>
                <h2>Add Windows Key</h2>
                <p>Windows Key: </p><input type="text" id="keyInput"><br>
                <p>Windows Version: </p><select name="keyVer" id="keyVer"></select><br>
                <a id="addMac" onclick="addKey()"><h2>Save</h2></a>
            </div>
        </div>
        
        <div class="modal" id="deleteConfirm" style="display: none;">
            <div class="modal-content" id="modal-content">
                <span class="close" onclick="hideModal('deleteConfirm')">x</span>
                <h2>Delete</h2>
                <p><div id="deleteTarget"></div></p>
            <a id="confirmDelete" onclick="confirmDelete(row[0], table)"><h2>confirm</h2></a>
            </div>
        </div>
        
        <div class="modal" id="editCell" style="display: none">
            <div class="modal-content" id="modal-content">
                <span class="close" onclick="hideModal('editCell')">x</span>
                <h2> Edit Cell </h2>
                <select id="cellSelect"></select>
                <div id="freeable" style="display: none">
                    <input type="submit" class="button" id="free_asset" value="Free" onclick="freePK(row[0], table)">
                </div>
                <a id="saveEdit" onclick="saveSelect(task,row,current,table)"><h2>Save</h2></a>
            </div>
        </div>
        
        <div class="modal" id="vendorModelModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="hideModal('vendorModelModal')">x</span>
                <p>
                    <select id="vendors"></select>
                    <select id="models"></select>
                </p>
                <a id="saveModel" onclick="vendorModelUpdate(row, current)"><h2>Save</h2></a>
            </div>
        </div>
        
        <div class="modal" id="addmodelsModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="hideModal('addmodelsModal')">x</span>
                <h2>Add a new Model</h2>
                Select a vendor for the new model from the list:
                    <select id="vendors_new"></select>
                    <input type="text" id="newModel"><input type="submit" class="button" id="addNewModel" value="Add new model" onclick="addModel()">
                </p>
            </div>
        </div>
        
        <div class="modal" id="noteModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="hideModal('noteModal')">x</span>
                <p><div id="hostNotes"></div></p>
                <p>
                    <textarea id="newNote" maxlength="200" rows="3" cols="30"></textarea>
                    <input type="submit" class="button" id="newNoteSubmit" value="Add new note" onclick="addNote(host)">
                </p>
            </div>
        </div>
        
        <div class="modal" id="addvendorModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="hideModal('addvendorModal')">x</span>
                <p>
                Edit Vendors:
                <div id="miniVendors"></div>
                Add a new Vendor:
                    <input type="text" id="newVendor"><input type="submit" class="button" id="addNewVendor" value="Add new vendor" onclick="addVendor()">    
                </p>
            </div>
        </div>
        
        <div class="modal" id="addhardwareModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="hideModal('addhardwareModal')">x</span>
                <p>
                <h2>Add Hardware</h2>
                hostname:
                    <select id="hardware"></select>
                <a id="saveHardware" onclick="addHardware()"><h2>Save</h2></a>
                </p>
            </div>
        </div>
        
        <div class="tab">
            <button class="tablinks" onclick="load(hostTable)">Hosts</button>
            <button class="tablinks" onclick="load(keysTable)">Windows Keys</button>
            <button class="tablinks" onclick="load(macsTable)">MAC Addresses</button>
            <button class="tablinks" onclick="load(vmTable)">Vendors and Models</button>
            <button class="tablinks" onclick="load(hwTable)">Hardware</button>
        </div>
        
        <div id="ops" class="alert" style="display: none;">
            <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
            <div id="ops_results"></div>
        </div>
        
        <div id="tableDisplay"></div>
        
        <script> 
            // init
            var tables;
            var ht;
            var hostData;
            var keyData;
            var dbData;
            var hostTable;
            var keysTable;
            var macsTable;
            var vmTable;
            var hwTable;
            
            // ui
            var current;
            var row;
            var selectedOption;
            var table;
            var field;
            var task;
            var host;
            
            // filter currently displayed table via filter query
            $(document).ready(function(){
                $("#filter_box").on("keyup", function(){
                    filter();
                });    
            });
            
            window.onload = function(){
                load();
                $("#tableDisplay").html(hostTable);
                $("#filter_box").val(localStorage.getItem("filter"));
                filter();
            };
            
            /*
            function load(thisTable)
            {
                dbData = getData();
                tables = dbData.tables;
                ht = dbData.ht;
                hostData = dbData.hostdata;
                keyData = dbData.keydata;
                hostTable = dbData.hosttable;
                keysTable = dbData.keystable;
                macsTable = dbData.macstable;
                vmTable   = dbData.vmtable;
                hwTable   = dbData.hwtable;
                $("#tableDisplay").html(thisTable);
                filter();
            };*/
            
            function load(thisTable)
            {
                var get = initTables();
                dbData = get.data;
                tables = dbData.tables;
                ht = dbData.ht;
                hostData = constructHosts();
                keyData = constructKeys();
                hostTable = get.inittables.hosttable;
                keysTable = get.inittables.keystable;
                macsTable = get.inittables.macstable;
                vmTable   = get.inittables.vmtable;
                hwTable   = get.inittables.hwtable;
                $("#tableDisplay").html(thisTable);
                filter();
            }
            
            $("#vendors").change(function() 
            {
                $("#models").html(makeModelSelect(tables.models));
            });
            
            $(document).on('click', '.deleteRow', function(e)
            {
               var pk    = $(this).closest('td').text();
               var table = $(this).closest('table').attr('id');
               console.log('delete row; pk: ' + pk + ', table: ' + table);
               deleteRow(pk, table);
            });
            
            $(document).on('click', '.addRow', function(e)
            {
                console.log($(this).closest('table').attr('id'));
                var cell = "add" + $(this).closest('table').attr('id') + "Modal";
                showModal(cell);
            });
            
            $(document).on('click', '.addVendor', function(e){
               showModal("addvendorModal"); 
            });
            
            $(document).on('click', '.deleteNote', function(){
                var host = $(this).closest('td').attr('id');
                var note = $(this).closest('td').text();
                note = escapeHTML(note);
                deleteNote(note, host, tables);
            });
            
            $(document).on('click', '.deleteVendor', function(){
               var vendor = $(this).closest('td').text();
               confirmDelVendor(vendor);
            });
            
            $(document).on('click', 'td', function(e)
            {
                current = $(this).text();
                table = $(this).closest('table').attr('id');
                field = $(this).attr('id');
                row     = rowToArray($(this).closest('tr')); 
                task = getEditFunction(table, field, tables);
                
                console.log("row pk: " + row[0] + " field: " + field + " table: " + table);
                
                if(task.select !== false)
                {
                    if(task.freeable)
                    {
                        $("#freeable").css("display", "block");
                        document.getElementById("free_asset").value = "Free " + row[0] + "? (set " + field + " to none)"; 
                    }
                    else
                    {
                        $("#freeable").css("display", "none");
                    }
                    
                    var options = task.select;
                    $("#cellSelect").html(options);
                    $("#" + task.modal).css("display", "block");
                }
                else
                {
                    if(task.editable)
                    {
                        $(this).closest('td').attr('contenteditable', 'true');
                        $(this).focusin();
                    }
                    if(task.tempTable)
                    {
                        host = row[0];
                        makeNoteTable(tables.note, tables.hosts, host);
                        $("#" + task.modal).css("display", "block");
                    }
                }
            });
                 
            $(document).on('focusout', 'td', function(e)
            {
                $(this).css("background", "lightgray");
                $(this).closest('td').attr('contenteditable', 'false');
                var newdata  = $(this).text();
                if(newdata !== current)
                {
                    saveCell(task, current, newdata, row, table, field);
                }    
            });
        </script>
    </body>
</html>
