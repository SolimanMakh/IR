<?php 

class Letter
{   
    private $frequency = 0;
    private $Tf = 0; 
    private static $Df = array(0,0,0,0,0); 
    private static $idf=array(0,0,0,0,0);
    private $letterAlreadyCounted=false; 
    

    public function __construct()
    {
         $this->letterAlreadyCounted = 0; 
    }
    public function incrementFreq()
    {
        $this->frequency++; 
    }
    
    public function calculateTf($max)
    { 
      $this->Tf = $this->frequency/$max;   
    }
   public  function incrementDf($L) 
    {
        if (!$this->letterAlreadyCounted)
        {
            self::$Df[$L]++; 
            $this->letterAlreadyCounted = true;  
        }
            
    }
      public static function calculateIDF($numberOfDocuments,$L)
      {
        self::$idf[$L] = log10($numberOfDocuments/self::$Df[$L]);
      }
     
      public function getFreq() 
      {
          return $this->frequency;  
      } 
      public function getTf() 
      {
          return $this->Tf;  
      } 
         public static function getidf($Char) 
      {
          return self::$idf[$Char];  
      } 
    
}





class File 
{
    private $fileVar; 
    private $fileSize;
    private static $numberOfDocuments = 0;
    private $Letters;
    private $max; 
    public function __construct()
    {  
        $this->Letters = Array(new Letter(),new Letter(),new Letter(),new Letter(),new Letter());
        Self::$numberOfDocuments++;
        if(!file_exists("Document".self::$numberOfDocuments.".txt")) 
           {
        $this->fileSize = mt_rand(1,7); 

        $Alphabet = array('A','B','C','D','E');  
        $this->fileVar = fopen ("Document".self::$numberOfDocuments.".txt", "w+");


        for ($i = 0 ; $i< $this->fileSize ; $i++)
        {
            $RandomChar = mt_rand(0,4); 
            $bytes_written = fwrite($this->fileVar, $Alphabet[$RandomChar]);
        }


          } 
        
         $this->fileVar = fopen ("Document".self::$numberOfDocuments.".txt", "r");
        
        
        
        while(!feof($this->fileVar)) 
        {           $M = ord(fgetc($this->fileVar));
                    $S =  $M - 97;
                    if ($S < 0)
                     $S = $M - 65;
         
                    if ($S >=0 && $S < 5)
                    {
                        $this->Letters[$S]->incrementFreq();
                        $this->Letters[$S]->incrementDf($S); 
                    }
        
        }
        
       $this->max = max($this->Letters[0]->getFreq(),$this->Letters[1]->getFreq(),$this->Letters[2]->getFreq(),$this->Letters[3]->getFreq(),
                 $this->Letters[4]->getFreq());
        
        for($x = 0 ; $x < 5;  $x++)
        $this->Letters[$x]->calculateTf($this->max);
   
    }
    public static function calculateIDF()
    {  for($x = 0 ; $x < 5 ; $x++)
        Letter::calculateIDF(self::$numberOfDocuments,$x); 
    }
    public function getIdf($Char)
    {
      return $this->Letters[$Char]->getidf($Char);
    }
    public function getTf($Char)
    {
        return $this->Letters[$Char]->getTf();
    } 
}

class Query 
{
    private $Q;  
    private $regularExp ;
    private $Letters;
    private $qLength;
    private $max ; 
    public function __construct() 
    {
        
        $this->Letters = Array(new Letter(),new Letter(),new Letter(),new Letter(),new Letter());
        $this->Q = $_GET['Query'];
        $this->Q = str_replace(" ","",$this->Q);
        $this->qLength = strlen($this->Q);
        $this->regularExp = '/^[A-Ea-e]+$/' ;
        if (!preg_match($this->regularExp,$this->Q))
        {    
            header('Location: http://localhost/Project2/Error.php');
            exit(); 
        }
        
      for ($x = 0 ; $x < $this->qLength ; $x++)
      {   $M =ord($this->Q[$x]);
          $S = $M - 97; 
           if ($S < 0)
               $S = $M - 65; 
          
          $this->Letters[$S]->incrementFreq();
      }
     
  $this->max = max($this->Letters[0]->getFreq(),$this->Letters[1]->getFreq(),$this->Letters[2]->getFreq(),$this->Letters[3]->getFreq(),
                 $this->Letters[4]->getFreq());
     
      for ($x = 0 ; $x < 5 ; $x++)
      {
          $this->Letters[$x]->calculateTf($this->max);
      }     
    } 
    public function getIdf($Char)
    {
      return $this->Letters[$Char]->getidf($Char);
    }
    public function getTf($Char)
    {
        return $this->Letters[$Char]->getTf();
    } 
 
}


class VectorModel 
{
  private $Q; 
  private $Files;
  private $fileCount; 
  private $DotProSum;
  private $CosineSim; 
  public function __construct($Files)
  {
      $this->DotProSum = array();
      $this->CosineSim= array(); 
      $this->Q = new Query();
      $this->Files = $Files;
      $this->fileCount = sizeof($this->Files);
      File::calculateIDF();
  }

  public function cosSimilarityCalculation()
  {
      for($x = 0 ; $x< $this->fileCount ; $x++)
      {   
          $DotProTemp = 0;
          $SquaredWeightF = 0;
          $SquaredWeightQ = 0;
          $CosineSimTemp = 0 ; 
          for ($y = 0 ; $y < 5 ; $y++)
          {
              $DotProTemp += $this->Files[$x]->getIdf($y) * $this->Files[$x]->getTf($y) * $this->Q->getIdf($y) * $this->Q->getTf($y);
              $SquaredWeightF += pow($this->Files[$x]->getTf($y) * $this->Files[$x]->getIdf($y),2); 
              $SquaredWeightQ += pow($this->Q->getIdf($y) * $this->Q->getTf($y),2);
              //echo $SquaredWeightF. "     " .$SquaredWeightQ; "  ". $DotProTemp . "    ";
             // echo $this->Files[$x]->getIdf($y) . "     " ;

          }
          if($SquaredWeightF !=0 && $SquaredWeightQ !=0)
         $CosineSimTemp = $DotProTemp/sqrt($SquaredWeightF * $SquaredWeightQ);
         
         array_push($this->DotProSum,$DotProTemp);
         array_push($this->CosineSim,$CosineSimTemp);

      }
      arsort($this->CosineSim);
     foreach ($this->CosineSim as $docNum=>$value)
         echo ($docNum+1). " - " .$value ."<br>" ;  
  }
    
} 

$VectModel = new VectorModel(array(new File,new File , new File));
$VectModel->cosSimilarityCalculation(); 




?> 