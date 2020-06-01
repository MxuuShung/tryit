<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <script>
Math.random().toString(36).substr(2,5);
Date.now().toString(36).substr(4,6);
$('#random_verification').click(
function GenNonDuplicateID(){
return Math.random().toString(36).substr(2,5) +  Date.now().toString(36).substr(4,6);
});
    console.log(GenNonDuplicateID());
    document.write(GenNonDuplicateID());


    
    </script>
        <script>
        Math.random().toString(36).substr(2, 5);
        Date.now().toString(36).substr(4, 6);
        $('button#random_verification').click(
            function get_ver() {
                return Math.random().toString(36).substr(2, 5) + Date.now().toString(36).substr(4, 6);
                document.write(get_ver());
            });
            
    </script>

    <button id="random_verification">click me</button>
</body>
</html>
