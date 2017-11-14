<?php  

$File1Size = mt_rand(1,7); 
$File2Size = mt_rand(1,7); 
$File3Size = mt_rand(1,7); 

$Alphabet = array('A','B','C','D','E');  
$file_var1 = fopen ("Document1.txt", "w+");
$file_var2 = fopen ("Document2.txt", "w+");
$file_var3 = fopen ("Document3.txt", "w+");

for ($i = 0 ; $i<$File1Size ; $i++)
{
    $RandomChar = mt_rand(0,4); 
    $bytes_written = fwrite($file_var1, $Alphabet[$RandomChar]);
}

for ($i = 0 ; $i<$File2Size ; $i++)
{
      $RandomChar = mt_rand(0,4); 
    $bytes_written = fwrite($file_var2, $Alphabet[$RandomChar]);
}

for ($i = 0 ; $i<$File3Size ; $i++)
{
    $RandomChar = mt_rand(0,4); 
    $bytes_written = fwrite($file_var3, $Alphabet[$RandomChar]);
}


/*echo "$File1Size " ;
echo "$File2Size ";
echo "$File3Size"; */

header('Location: http://localhost/Project2/Search.html');




?>