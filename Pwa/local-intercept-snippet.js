/**
 * MageMasani BannerSlider - Venia PWA Studio Targetables Interceptor
 * 
 * Add the following code block inside your PWA Studio project's `local-intercept.js`
 * inside the `localIntercept(targets)` function.
 */

const { Targetables } = require('@magento/pwa-buildpack');

function localIntercept(targets) {
    const targetables = Targetables.using(targets);

    // Target the core CMS page component to add BannerSlider on the home page
    const cmsPageComponent = targetables.reactComponent(
        '@magento/venia-ui/lib/RootComponents/CMS/cms.js'
    );

    // Import our custom BannerSlider component
    // Note: Adjust the relative import path depending on where you place your component folder
    cmsPageComponent.addImport(
        "import BannerSlider from '../../../../../../src/components/BannerSlider/bannerSlider.js'"
    );

    // Insert BannerSlider directly above RichContent on home page
    cmsPageComponent.insertBeforeJSX(
        'RichContent html={content}',
        "<Fragment>{(identifier === 'home' || identifier === 'venia-new-home') ? <BannerSlider /> : null}</Fragment>"
    );
}

module.exports = localIntercept;
