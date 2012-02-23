<?php
$dir = "../install";
//this sctipt don't delete subfolders
if(@$open = opendir($dir))
{
        while($file = readdir($open)){ 
             if($file == "." || $file == "..") 
                  continue; 
             if(!unlink("$dir/$file")) 
				{
				  die("$dir/$file:error deleting the file");
				}			
        }
	
	    if(!rmdir($dir))
        {
		  die("$dir:error closing the folder");
		}	

        header("Location: ../../index.html");		
   
   
}else{
die("any installation folder found");
}		
?>