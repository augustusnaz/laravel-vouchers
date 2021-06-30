/**
 * Welcome to your Workbox-powered service worker!
 *
 * You'll need to register this file in your web app and you should
 * disable HTTP caching for this file too.
 * See https://goo.gl/nhQhGp
 *
 * The rest of the code is auto-generated. Please don't update this file
 * directly; instead, make changes to your Workbox build configuration
 * and re-run your build process.
 * See https://goo.gl/2aRDsh
 */

importScripts("https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js");

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [
  {
    "url": "404.html",
    "revision": "89f1e3fb05cb69466a1c9ed5768f5d56"
  },
  {
    "url": "assets/css/0.styles.c2c66dca.css",
    "revision": "5fa672cdbc5b9f58468d1f630386058e"
  },
  {
    "url": "assets/img/search.83621669.svg",
    "revision": "83621669651b9a3d4bf64d1a670ad856"
  },
  {
    "url": "assets/js/10.90133196.js",
    "revision": "9bb1cc6d17cafbedc6a35e86940f7903"
  },
  {
    "url": "assets/js/11.2f4c7f68.js",
    "revision": "eaa5b42a5d8fdd1ae6e24bc5082b4dd7"
  },
  {
    "url": "assets/js/12.0a9c28dd.js",
    "revision": "c15c283fbcdef429e5f487e4edbc7e05"
  },
  {
    "url": "assets/js/13.70c26686.js",
    "revision": "f5a5e0cf3bcb91b9e9830f2d2226a73c"
  },
  {
    "url": "assets/js/14.1cc58e1e.js",
    "revision": "43091c456900b9c48b5c5330444a92a8"
  },
  {
    "url": "assets/js/15.aa6f7888.js",
    "revision": "2475f15e81d4d579ae3d613bd78c80d6"
  },
  {
    "url": "assets/js/16.62acec7f.js",
    "revision": "91a83a224f4af9c2289131339aaf65c2"
  },
  {
    "url": "assets/js/17.67270562.js",
    "revision": "4b4912967e279f37fb7bfd474a2279f8"
  },
  {
    "url": "assets/js/18.0fc71d62.js",
    "revision": "8c0045611fe0fe1056d87b67deaf32bf"
  },
  {
    "url": "assets/js/19.c9d43b85.js",
    "revision": "28256e1b3008749a5f77e10983850ae2"
  },
  {
    "url": "assets/js/2.0a4b0f83.js",
    "revision": "cbed2ed15481b149e47d4faab59abf1b"
  },
  {
    "url": "assets/js/20.0ea25666.js",
    "revision": "9ad97a43207f43d7208c83de561db81f"
  },
  {
    "url": "assets/js/21.b3241ea4.js",
    "revision": "af90755b74b0cac019bdbfb149a31768"
  },
  {
    "url": "assets/js/3.535b8b94.js",
    "revision": "b1924126831c6d9c4ffd0ad730ab97a2"
  },
  {
    "url": "assets/js/4.d2008d69.js",
    "revision": "76d0a93a2e7e985e33f7d95471b5c165"
  },
  {
    "url": "assets/js/5.40051e47.js",
    "revision": "329e569395424a4181866bd3d549d6ae"
  },
  {
    "url": "assets/js/6.10d24408.js",
    "revision": "3486daf8876e68f8f2c47eb0cd611478"
  },
  {
    "url": "assets/js/7.2143f453.js",
    "revision": "7d95a487a4ada9e2e4abc1be2498cf32"
  },
  {
    "url": "assets/js/8.681796f1.js",
    "revision": "877bf0e9e52c1b700a5ba5aa96804fd9"
  },
  {
    "url": "assets/js/9.9730230e.js",
    "revision": "4555dcee6a45bfd435f9f94b90d64e69"
  },
  {
    "url": "assets/js/app.5d6e800b.js",
    "revision": "f3e5c318ee3d8f394f51db0c32aef3cb"
  },
  {
    "url": "concept.html",
    "revision": "c50828847e5edc8c619a83c1a71c9d60"
  },
  {
    "url": "configuration.html",
    "revision": "069193f449771eae786c04f75a6d2613"
  },
  {
    "url": "errors.html",
    "revision": "861afd459743923d846f5fac9f000347"
  },
  {
    "url": "events.html",
    "revision": "c2621ec51617fe461866a19f7819423a"
  },
  {
    "url": "features.html",
    "revision": "b58b04297c2479808e1de8da90482fe7"
  },
  {
    "url": "index.html",
    "revision": "0cac40785cbb709d0d3a1d3fda69cbb0"
  },
  {
    "url": "installation/index.html",
    "revision": "9e506fcb9fb795a2c05cae46e0e1f26d"
  },
  {
    "url": "installation/nova-example.html",
    "revision": "6df0b54e6d26914b6662bf7443ecfaab"
  },
  {
    "url": "usage/create.html",
    "revision": "f890f46fb1695d662048b882474032b8"
  },
  {
    "url": "usage/data.html",
    "revision": "08c1470fe8b192ae1accc397cf5460d3"
  },
  {
    "url": "usage/limits.html",
    "revision": "c89bddff9db0e5f12f0091ce42bb3157"
  },
  {
    "url": "usage/redeem.html",
    "revision": "99d24c42d796f1ee1f6b467012fd6b56"
  },
  {
    "url": "usage/relationships.html",
    "revision": "d294ea01e77ee1339c6daedd5030ffb1"
  }
].concat(self.__precacheManifest || []);
workbox.precaching.precacheAndRoute(self.__precacheManifest, {});
addEventListener('message', event => {
  const replyPort = event.ports[0]
  const message = event.data
  if (replyPort && message && message.type === 'skip-waiting') {
    event.waitUntil(
      self.skipWaiting().then(
        () => replyPort.postMessage({ error: null }),
        error => replyPort.postMessage({ error })
      )
    )
  }
})
