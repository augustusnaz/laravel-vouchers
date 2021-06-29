module.exports = {
  title: "Laravel Vouchers",
  description: "Manage ecommerce vouchers.",
  base: "/laravel-vouchers/",
  themeConfig: {
    // logo: "/logo.png",
    repo: "augustusnaz/laravel-vouchers",
    repoLabel: "Github",
    docsRepo: "augustusnaz/laravel-vouchers",
    docsDir: "docs",
    docsBranch: "master",
    sidebar: [
      {
        title: "Get started",
        collapsable: false,
        children: [
          "/features",
          "/installation/",
          "/configuration",
          "/installation/nova-example",
        ],
      },
      {
        title: "Usage",
        collapsable: false,
        children: [
          "/usage/create",
          "/usage/limits",
          "/usage/redeem",
          "/usage/data",
          "/usage/relationships",
        ],
      },
      {
        title: "Handling Errors",
        path: "/errors",
      },
      {
        title: "Events",
        path: "/events",
      },
    ],
    nav: [{ text: "Home", link: "/" }],
  },
  head: [
    // ["link", { rel: "icon", href: "/logo.png" }],
    // ['link', { rel: 'manifest', href: '/manifest.json' }],
    ["meta", { name: "theme-color", content: "#3eaf7c" }],
    ["meta", { name: "apple-mobile-web-app-capable", content: "yes" }],
    [
      "meta",
      { name: "apple-mobile-web-app-status-bar-style", content: "black" },
    ],
    // ["link", { rel: "apple-touch-icon", href: "/icons/apple-touch-icon.png" }],
    // ['link', { rel: 'mask-icon', href: '/icons/safari-pinned-tab.svg', color: '#3eaf7c' }],
    // [
    //   "meta",
    //   {
    //     name: "msapplication-TileImage",
    //     content: "/icons/android-chrome-192x192.png",
    //   },
    // ],
    ["meta", { name: "msapplication-TileColor", content: "#000000" }],
  ],
  plugins: [
    "@vuepress/register-components",
    "@vuepress/active-header-links",
    "@vuepress/pwa",
    [
      "@vuepress/search",
      {
        searchMaxSuggestions: 10,
      },
    ],
    "seo",
  ],
};
