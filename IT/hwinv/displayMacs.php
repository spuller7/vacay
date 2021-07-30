<html>
    <head>
        <title>MAC Addresses</title>
        <meta charset="UTF-8">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <?php
            require "connector.php";
            
            $connection = new DBcon();
            $ht = $connection->getHelperTables();
            $macHostname = $ht["macHostname"];
            $connection->close();
        ?>
        
        <a href="index.php"><h3>Home</h3></a>
        
        <input type="text" id="searchMacsbox">
        <input type="submit" class="button" id="mac_search" value="Search MACs">
        <div id="mac_results"></div>
        
        <h1>MAC Addresses</h1>
        <table>
            <tr>
                <th>MAC Address</th>
                <th>hostname</th>
            </tr>
            <?php foreach ($macHostname as $i => $row): ?>
            <tr>
               <td><?php echo htmlspecialchars($macHostname[$i]['macAddress']) ?></td>
               <td><?php echo htmlspecialchars($macHostname[$i]['hostname']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <script>    
            var macHostname  = '<?php echo json_encode($ht["macHostname"]); ?>';
            
            $("#mac_search").click(function(){
                var search = $("#searchMacsbox").val().trim();
                console.log(search);

                $.ajax({
                    type: "POST",
                    url: "searchmacs.php", 
                    data: {search:search, macHostname:macHostname},
                    success: function(Data){
                        console.log('MAC search success');
                        var results = JSON.parse(Data);
                        $("#mac_results").html('');
                        $("#mac_results").append(results.q + ": " + results.results);   
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
