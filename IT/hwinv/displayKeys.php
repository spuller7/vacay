<html>
    <head> 
        <title>Windows Keys</title>
        <meta charset="UTF-8">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <?php
            require "functions.php";
            require "connector.php";
            
            $connection = new DBcon();
            $ht = $connection->getHelperTables();
            $keyData = buildKeys($connection->getAll(), $ht);
            $connection->close();
        ?>
        
        <a href="index.php"><h3>Home</h3></a>
        
        <input type="text" id="searchKeysbox">
        <input type="submit" class="button" id="key_search" value="Search Keys">
        <div id="key_results"></div>
        
        <h1>Windows Keys</h1>
        <table class="table table-bordered table-condensed">
            <tr>
                <th>Key</th>
                <th>Version</th>
                <th>In Use on Host</th>
                <th>Sticker Host</th>
                <th>License free?</th>
                <th>Verification Status</th>
            </tr>
            <?php foreach ($keyData as $i => $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($keyData[$i]['winKey']) ?></td>
                <td><?php echo htmlspecialchars($keyData[$i]['version']) ?></td>
                <td><?php echo htmlspecialchars($keyData[$i]['host']) ?></td>
                <td><?php echo htmlspecialchars($keyData[$i]['sticker']) ?></td>
                <td><?php echo htmlspecialchars($keyData[$i]['free']) ?></td>
                <td><?php echo htmlspecialchars($keyData[$i]['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <script>
            var keyHosts = '<?php echo json_encode($ht["hostKeys"]); ?>';
            
            $("#key_search").click(function(){
                var search = $("#searchKeysbox").val().trim();
                console.log(search);
                
                $.ajax({
                   type:"POST",
                   url: "searchkeys.php",
                   data: {search:search, keyHosts:keyHosts},
                   success: function(Data){
                       console.log('key search success');
                       var results = JSON.parse(Data);
                       $("#key_results").html('');
                       $("#key_results").append(results.q + ": " + results.results);
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
