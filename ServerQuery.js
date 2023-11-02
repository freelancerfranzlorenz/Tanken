/**
 *  This class supports
 */

class ServerQuery
{
  //sServerURL = "http://127.0.0.1/tanken/database.php";
  sServerURL = "https://franzweb.lima-city.de/Tanken/database.php";
  bDebug = true; //false;
  
  constructor()
  {
    console.log( "ServerQuery: serverURL='"+this.sServerURL+"'" );
  }

  get( sPlz, sSprit, sRadius, fctCallback ) 
  {
    this.xhttp = new XMLHttpRequest();
    this.xhttp.onload = function() { fctCallback( this.responseText ); };
    let sReq = "";
    sReq += "plz="+encodeURI( sPlz );
    sReq += "&sprit="+encodeURI( sSprit );
    sReq += "&radius="+encodeURI( sRadius );
    if( this.bDebug ) { console.log( "ServerQuery:get():request '"+sReq+"'" ); }
    this.xhttp.open( "POST", this.sServerURL, true );
    this.xhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    this.xhttp.send( sReq );
  }

  decode( sServerText )
  {
    let Rcv = null;
    if( this.bDebug ) { console.log( "ServerQuery:decode():sServerText '"+sServerText+"'" ); }
    try
    {
      Rcv = JSON.parse( sServerText );
      Rcv.dat = JSON.parse( decodeURIComponent( Rcv.dat ) ); 
    }
    catch( Error )
    { };
    return Rcv;
  }

  setArray( sType, aData, fctCallback )
  {
    this.set( sType, JSON.stringify( aData ), fctCallback );
  }
  
} //class ServerStorage
