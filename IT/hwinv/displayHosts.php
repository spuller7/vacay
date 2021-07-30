<html>
    <head>
        <title>Hosts</title>
        <meta charset="UTF-8">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        
        <?php
            require "functions.php";
            require "connector.php";
            
            $connection = new DBcon();
            $tables = $connection->getAll();
            $hostData = buildHosts($tables, $connection->getHelperTables());
            $connection->close();
        ?>
        
        <a href="index.php"><h3>Home</h3></a>
        
        <input type="text" id="search_box">
        <input type="submit" class="button" id="search_button" value="Search Hosts">
        <div id="results"></div>
        
        <h1>Hosts</h1>
        <table class="table table-bordered table-condensed">
            <tr>
                <th>Hostname</th>
                <th>Hardware</th>
                <th>Type</th>
                <th>Property Tag</th>
                <th>Service Tag or Serial Number</th>
                <th>MAC(s)</th>
                <th>Current User</th>
            </tr>
            <?php foreach ($hostData as $i => $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($hostData[$i]['hostname']) ?></td>
                <td><?php echo htmlspecialchars($hostData[$i]['hardware']) ?></td>
                <td><?php echo htmlspecialchars($hostData[$i]['type']) ?></td>
                <td><?php echo htmlspecialchars($hostData[$i]['tag']) ?></td>
                <td><?php echo htmlspecialchars($hostData[$i]['STSN']) ?></td>
                <td><?php foreach ($hostData[$i]["MACs"] as $m => $key) echo htmlspecialchars($hostData[$i]['MACs'][$m]) . "\n"?></td>
                <td><?php echo htmlspecialchars($hostData[$i]['user']) ?></td>   
            </tr>
            <?php endforeach; ?>
        </table>
        
        <script>    
            var hosts  = '<?php echo json_encode($tables["hosts"]); ?>';
            
            $("#search_button").click(function(){
                var search = $("#search_box").val().trim();
                console.log(search);
                console.log(hosts);
                
                $.ajax({
                    type: "POST",
                    url: "search.php", 
                    data: {search:search, hosts:hosts},
                    success: function(Data){
                        console.log('host search success');
                        var results = JSON.parse(Data);
                        $("#results").html('');
                        $("#results").append(results.q + ": " + results.results);   
                    },
                    error: function(error){
                        console.error(error);
                    }
                });
            });      
        </script>
        
        <script>
            window.onload = function(){
                var getCellValue = function(tr, idx){ return tr.children[idx].innerText || tr.children[idx].textContent; }

                var comparer = function(idx, asc) { return function(a, b) { return function(v1, v2) {
                    return v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2);
                    }(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));
                }};


                Array.prototype.slice.call(document.querySelectorAll('th')).forEach(function(th) { th.addEventListener('click', function() {
                var table = th.parentNode;
                while(table.tagName.toUpperCase() !== 'TABLE') table = table.parentNode;
                Array.prototype.slice.call(table.querySelectorAll('tr:nth-child(n+2)'))
                    .sort(comparer(Array.prototype.slice.call(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                    .forEach(function(tr) { table.appendChild(tr) });
                    })
                });
            };           
        </script>
        
    </body>
</html>