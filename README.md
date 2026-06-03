# MageMasani Banner Slider & Venia PWA Integration

This package contains the fully optimized Banner Slider module for Magento 2 and its companion integration for Magento Venia PWA Studio.

---

## 1. Directory Structure

* **`BannerSlider/`**: Core Magento backend module managing database persistence, banners, sliders, media uploads, and backend admin UI.
* **`BannerSliderGraphQl/`**: Exposes the data via optimized GraphQL schemas and query resolvers.
* **`Pwa/`**: Frontend PWA integration files.
  * **`BannerSlider/`**: React components.
    * `bannerSlider.js`: Slider carousel logic, autoplay, fallback rendering, and dynamic product/category routing resolution.
    * `bannerSlider.gql.js`: Apollo GraphQL queries for retrieving banners (`BannerSliderInfo`) and resolving SEO/routing URLs for products and categories.
    * `bannerSlider.module.css`: CSS modules stylesheet defining responsive grid layouts and fluid transitions.
  * **`local-intercept-snippet.js`**: Reference Targetables code to register and render `<BannerSlider />` dynamically on the Venia CMS homepage without touching Venia source code.

---

## 2. Backend Installation Steps (New Project)

1. **Upload Files**:
   Copy the `BannerSlider` and `BannerSliderGraphQl` directories to the target Magento installation:
   ```bash
   app/code/MageMasani/BannerSlider
   app/code/MageMasani/BannerSliderGraphQl
   ```

2. **Enable Magento Modules**:
   Register the modules and run setup upgrade, compilation, and cache flush:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush
   ```

---

## 3. Frontend PWA Studio Installation Steps

1. **Copy Components**:
   Create a folder in your PWA codebase:
   ```bash
   src/components/BannerSlider/
   ```
   Copy all files from `MageMasani/Pwa/BannerSlider/` into that directory.

2. **Register the Component via Targetables**:
   Open the target PWA project's root `local-intercept.js` file and add the targetables code from `MageMasani/Pwa/local-intercept-snippet.js`:
   * Require `Targetables` from `@magento/pwa-buildpack` if not already imported.
   * Target `@magento/venia-ui/lib/RootComponents/CMS/cms.js`.
   * Add the import and JSX insertion instructions to place the `<BannerSlider />` component dynamically on the homepage.

3. **Configure Caching**:
   For peak PWA performance, ensure the GraphQL query client uses **GET** requests for public queries. In your Apollo Client link configuration, configure it to route queries via GET. This allows Varnish Cache and browser caching to cache the responses.

---

## 4. How to Manage Banner Sliders in Magento Admin

To display banners on your PWA, configure the slider and banners in the Magento Admin Panel:

### Step 1: Enable the Module
1. Navigate to **Stores** -> **Configuration** -> **MageMasani** -> **Banner Slider**.
2. Set **Enable** to **Yes**.
3. Save Config.

### Step 2: Create a Slider
1. Navigate to **Content** -> **Banner Slider** -> **Manage Sliders**.
2. Click **Add New Slider**.
3. Fill out properties (Title, Identifier, etc.) and set status to **Enabled**.
4. Click **Save**.

### Step 3: Create Banners
1. Navigate to **Content** -> **Banner Slider** -> **Manage Banners**.
2. Click **Add New Banner**.
3. **General Info**:
   * Enter **Title** (displayed as slide heading).
   * Set **Status** to **Enabled**.
   * Assign it to your **Slider**.
   * Specify **Sort Order** (determines carousel sequence).
4. **Resource Info** (Choose one):
   * **Local Image**: Upload a file.
   * **External Image**: Provide a direct URL to an image.
   * **YouTube Video**: Provide a YouTube embed URL.
   * **Custom HTML**: Enter raw HTML code.
5. **Link Info** (Action when clicked):
   * **Product**: Select Product and specify the SKU or Product ID in the **Link Resource** field. The PWA will resolve the SEO-friendly URL path automatically.
   * **Category**: Select Category and specify the Category ID in the **Link Resource** field. The PWA will resolve the category path automatically.
   * **Custom**: Select Custom and provide a full link (e.g. `https://example.com/promo`) in the **Link Resource** field.
6. Click **Save Banner**.

---

## 5. Cache Warming (Post-Deployment)

To keep response times under 50ms, run the following cache-warming script immediately following deployment or cache flushes to pre-populate Varnish and OpCache for the home queries:

```bash
bash scripts/warm-cache.sh https://yourdomain.com
```
