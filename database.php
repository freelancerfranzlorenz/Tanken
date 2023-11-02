<?php

/**
 *  This is a simple database based on folder/files.
 *  The script uses POST data for input and 
 *  outputs a JSON encoded result.
 *
 *  The POST input parameters are:
 *  plz   string  postleitzahl
 *
 *  The JSON return parameters are:
 *  plz   string  copy of the input parameter "plz"
 *  dat   string  if command "get", then here the readen data
 *  err   string  error description, if any happens
 */ 

class Tankstelle
{
   public $sName;
   public $sStrasse;
   public $sOrt;
   public $sPreis;
   public $sRadius;
}

/**
 * This function gets the html code of a https like
 * page back.
 * @param  $sUrl    url to load
 * @param  $sOrt    ort of the query
 * @param  $sSprit  number/string of sprit
 * @param  $sRadius radius in km where to search
 * @return string   html string
 */
function getSSLPage( $sUrl, $sOrt, $sSprit, $sRadius ) 
{
   $sUrl = str_replace( "ort=", "ort=".$sOrt, $sUrl );
   $sUrl = str_replace( "spritsorte=", "spritsorte=".$sSprit, $sUrl );
   $sUrl = str_replace( "r=", "r=".$sRadius, $sUrl );
   //
   $ch = curl_init();
   curl_setopt( $ch, CURLOPT_HEADER, false );
   curl_setopt( $ch, CURLOPT_URL, $sUrl );
   curl_setopt( $ch, CURLOPT_SSLVERSION, 1 );
   //next line required for XAMPP (local)   
   // curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . "./cacert.pem");
   //next line required for limacity  
   curl_setopt( $ch, CURLOPT_CAINFO, "cacert.pem" );
   curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );   
   curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
   $sResult = curl_exec( $ch );
   $sError = curl_error( $ch );
   curl_close( $ch );
   return $sResult;
}

/**
 * This function returns the price-text
 * @param   $sLine   line to 
 * @return  string   price in format <euro>.<cent>
 *          null     if no valid price was found
 */
function getPrice( $sLine ) 
{
   $sRet = null;
   preg_match_all( '/\d+/', $sLine, $Matches );
   if( ( isset( $Matches[0][0] ) ) && ( isset( $Matches[0][1] ) ) )
      $sRet = $Matches[0][0].".".$Matches[0][1];
   return $sRet;
}

/**
 * This function returns the 
 * @return  string      inner string
 */
function getString( $sLine )
{
   $sRet = "";
   $nPos1 = strpos( $sLine, ">" );
   $nPos2 = strpos( $sLine, "<" );
   if( ( $nPos1 > 0 ) && ( $nPos2 > $nPos1 ) )
   {
      $nPos1++;
      $sRet = trim( substr( $sLine, $nPos1, $nPos2-$nPos1 ) );
   }
   return $sRet;
}

/**
 * This function evaluates a page of the insolvenzen and
 * writes the collected data into the global array $gInsolvenzen
 * of the class-type Insolvenz.
 * @param   $sHtml               html code of 
 * @return  array<Tankstelle>    array of class Tankstelle
 */
function evaluatePage( $sHtml )
{
   $Tankstellen = [];
   //
   $sHtml = str_replace( array( "\n", "\r", "\t" ), '', $sHtml );
   $nStart = strpos( $sHtml, "<div id=\"body-container\">" );
   if( $nStart > 0 )
   {
      $sHtml = substr( $sHtml, $nStart );
   }
   //
   $sHtml = str_replace( array( "<sup>", "</sup>" ), '', $sHtml );
   $aHtml = explode ( "class=", $sHtml );
   //
   // debug print_r( $aHtml );
   //
   $Tanke = null;
   foreach( $aHtml as $sLine )
   {
      if( strpos( $sLine, "price-text" ) > 0 )
      {
         $Tanke = new Tankstelle();
         $Tanke->sPreis = getPrice( $sLine );
         $Tanke->sName = "";
         $Tanke->sStrasse = "";
         $Tanke->sOrt = "";
      }
      else if( strpos( $sLine, "fuel-station-location-name" ) > 0 )
      {
         $sName = getString( $sLine );
         if( strlen( $sName ) > 0 )
         {
            $Tanke->sName = $sName;
         }
      }
      else if( strpos( $sLine, "fuel-station-location-street" ) > 0 )
      {
         $Tanke->sStrasse = getString( $sLine );
      }
      else if( strpos( $sLine, "fuel-station-location-city" ) > 0 )
      {
         $Tanke->sOrt = getString( $sLine );
         $Tankstellen[] = $Tanke;
      }
   }  //foreach()
   return $Tankstellen;
}

// --------------  MAIN -----------------------------------

// commands to allow CORS access
header( "Access-Control-Allow-Origin: *" );
header( "Access-Control-Allow-Methods: GET, POST" );
header( "Access-Control-Allow-Headers: X-Requested-With" );

// these are all parameters of a request
$aRequest = array( "plz"=>"", "sprit"=>"", "radius"=>"" );

// get all available keys/values from client request
$aKeys = array_keys( $aRequest );
foreach( $aKeys as $sKey )
{
  if( isset( $_POST[$sKey] ) )
    $aRequest[$sKey] = $_POST[$sKey];
  /*
  if( isset( $_GET[$sKey] ) )
    $aRequest[$sKey] = $_GET[$sKey];
  */
}

// setup return data array $aReturn
$aReturn  = $aRequest;
$aReturn["err"] = "";
$aReturn["result"] = "";

// check plz parameter
if( strlen( $aRequest["plz"] ) < 4 )
  $aReturn["err"] = "Ungültige Postleitzahl";
else if( strlen( $aRequest["sprit"] ) < 2 )
  $aReturn["err"] = "Ungültiger Sprit";
else if( strlen( $aRequest["radius"]) < 1 )
  $aReturn["err"] = "Ungültiger Radius";
else
{
  $sUrl = "https://www.clever-tanken.de/tankstelle_liste?lat=&lon=&ort=&spritsorte=&r=";
  $aSpritTable = array( 3=>"diesel", 7=>"supere5", 5=>"supere10" );
  $nSpritId = array_search( $aRequest["sprit"], $aSpritTable );
  //
  if( FALSE == $nSpritId )
  {
    $aReturn["err"] = "Sprittyp wird nicht unterstützt";
  }
  else
  {
    $sPlz = $aRequest["plz"];
    $sRadius = $aRequest["radius"];
    $sHtml = getSSLPage( $sUrl, $sPlz, $nSpritId, $sRadius );
    $aReturn["result"] = evaluatePage( $sHtml );
  }
}

//return the requested data or error
echo json_encode( $aReturn );
?>
