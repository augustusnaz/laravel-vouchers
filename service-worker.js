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
    "revision": "e768db26ee58968010391e68adf337dc"
  },
  {
    "url": "assets/css/0.styles.444572b0.css",
    "revision": "3f0119796c9b7bdb345573f1f1f650f5"
  },
  {
    "url": "assets/img/search.83621669.svg",
    "revision": "83621669651b9a3d4bf64d1a670ad856"
  },
  {
    "url": "assets/js/10.ef111839.js",
    "revision": "15a41e607e1d7c4332e85362f87befcd"
  },
  {
    "url": "assets/js/11.e8f6b6b8.js",
    "revision": "1fff5733d50292f0b221374c489b174b"
  },
  {
    "url": "assets/js/12.c8c9e1cf.js",
    "revision": "ce29b889e09523b3ff6e9efa1d6dfc40"
  },
  {
    "url": "assets/js/13.70c26686.js",
    "revision": "f5a5e0cf3bcb91b9e9830f2d2226a73c"
  },
  {
    "url": "assets/js/14.7b013b60.js",
    "revision": "f0f913562d14337a27f39e187bde15dc"
  },
  {
    "url": "assets/js/15.f5f918b0.js",
    "revision": "5ca57aa3f13c607a5a81a5bcb300f5c7"
  },
  {
    "url": "assets/js/16.d7eb0cc3.js",
    "revision": "e68db4874989555f142c843056a60fbf"
  },
  {
    "url": "assets/js/17.67270562.js",
    "revision": "4b4912967e279f37fb7bfd474a2279f8"
  },
  {
    "url": "assets/js/18.ac0b2881.js",
    "revision": "b0d3daf88419e56be9eb6a716480e73c"
  },
  {
    "url": "assets/js/19.71b89c99.js",
    "revision": "edeabb5ea0d91abe0e23d636451e155a"
  },
  {
    "url": "assets/js/2.0a4b0f83.js",
    "revision": "cbed2ed15481b149e47d4faab59abf1b"
  },
  {
    "url": "assets/js/20.ccdd2293.js",
    "revision": "181ea2e7591591c62e564d4bb44b220a"
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
    "url": "assets/js/4.a94a5e50.js",
    "revision": "ab696786f02096d4283488f227698413"
  },
  {
    "url": "assets/js/5.b8254b2c.js",
    "revision": "b2f83d4a323b1cccabcf6b780abb804c"
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
    "url": "assets/js/8.dd40b9ef.js",
    "revision": "be428bb149e64986b05d2229ff09e222"
  },
  {
    "url": "assets/js/9.27c057d1.js",
    "revision": "0ad6c54eb6f73b8d1b3f7a6535c2f94f"
  },
  {
    "url": "assets/js/app.af909db3.js",
    "revision": "1c4aad78dc8282c575bcc3fc1772ae07"
  },
  {
    "url": "concept.html",
    "revision": "96b168799fd1561ef3c002ebe9852d25"
  },
  {
    "url": "configuration.html",
    "revision": "dad33e762269d0999808e511d2fdb7c2"
  },
  {
    "url": "errors.html",
    "revision": "22549408761fa770b62f9021b6abe79c"
  },
  {
    "url": "events.html",
    "revision": "6075e5c93fa534b8058ae97ff7b6e8b2"
  },
  {
    "url": "features.html",
    "revision": "64f947c07a5104cbf3b1057ab40b4648"
  },
  {
    "url": "index.html",
    "revision": "80780bdd7d5e2bde18bffb25689c6cf6"
  },
  {
    "url": "installation/index.html",
    "revision": "4b1d1c0a790f27ea816f6734965385b5"
  },
  {
    "url": "installation/nova-example.html",
    "revision": "eda940650b86d0b42f34381c82534f4c"
  },
  {
    "url": "usage/create.html",
    "revision": "832b5ea4cc0d4c839c8270a3eb6aa5d2"
  },
  {
    "url": "usage/data.html",
    "revision": "6037be82a65ddf86a293d6bc80ab8b0d"
  },
  {
    "url": "usage/limits.html",
    "revision": "ecfdda039acd312bacfb3fa2f8d4bab6"
  },
  {
    "url": "usage/redeem.html",
    "revision": "f423b63cb9c4c972ebf5b383b2190eec"
  },
  {
    "url": "usage/relationships.html",
    "revision": "2ceeea5ab5f7b116c5682b92309ab619"
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
