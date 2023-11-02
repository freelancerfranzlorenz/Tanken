const CACHE_NAME = 'Tanken';
const assetsToCache = [
    './',
    './icon_48.png',
    './icon_144.png',
    './icon_192.png',
    './icon_512.png',
    './manifest.json',
    './index.html',
    './framework7-bundle.min.css',
    './framework7-bundle.min.js',
    './framework7-bundle.min.js.map',
    './ServerQuery.js',
    './navbaricon.svg',
    './navbariconr.svg'
];

self.addEventListener( 'install', function( event ) 
{   event.waitUntil( caches.open(CACHE_NAME)
      .then( function(cache) 
      {  return cache.addAll( assetsToCache ); } )
   );
} );

self.addEventListener('fetch', function(event) 
{  event.respondWith( caches.match(event.request)
      .then( function(response) 
      {  return response || fetch(event.request); } )
   );
} );
