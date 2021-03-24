describe('status 200 public', () => {

    const wgact_options_preset = Cypress.env('wgact_options_preset');
    const wgact_options_preset_conversion_cart_data_off     = Cypress.env('wgact_options_preset_conversion_cart_data_off');
    const wgact_options_preset_cookie_consent_fully_enabled = Cypress.env('wgact_options_preset_cookie_consent_fully_enabled');

    // seed options into database
    before(function () {
        // save current options to tmp file
        cy.exec('wp option get wgact_plugin_options --format=json --path=' + Cypress.env('wordpress_install_directory') + ' > ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json').its('code').should('eq', 0)

        // load preset
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    after(function () {
        // load from before test run
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json' + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    afterEach(() => {
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
            expect($html).to.not.contain('Undefined index')
        })
        cy.contains('Warning')
            .should('not.exist')
    })

    it('visit WC front page', () => {
        cy.visit('/')
    })

    it('visit WC front page with cookie consent fully enabled', () => {
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset_cookie_consent_fully_enabled + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
        cy.visit('/')
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    it('visit WC shop page', () => {
        cy.visit('/shop/')
    })

    it('visit WC product page: simple product', () => {
        cy.visit('/product/album/')
    })

    it('visit WC product page: variable product', () => {
        cy.visit('/product/hoodie/')
    })

    it('visit WC product page: variable product, all attributes set in filter', () => {
        cy.visit('/product/hoodie/?attribute_pa_color=blue&attribute_logo=Yes')
    })

    it('visit WC product page: variable product, partial attributes set in filter', () => {
        cy.visit('/product/hoodie/?attribute_pa_color=blue')
    })

    it('visit WC product page: external/affiliate product', () => {
        cy.visit('/product/wordpress-pennant/')
    })

    it('visit WC product page: grouped product', () => {
        cy.visit('/product/logo-collection/')
    })

    it('visit WC product category page', () => {
        cy.visit('/product-category/music/')
    })

    it('visit WC product tag page', () => {
        cy.visit('/product-tag/funny/')
    })

    it('visit WC cart page', () => {
        cy.visit('/cart/')
    })

    it('visit WC search attribute page', () => {
        cy.visit('/shop/?color=Blue')
    })

    it('visit WC purchase confirmation generic page', () => {
        cy.visit('/checkout/order-received/')
    })

    it('visit WC purchase confirmation page', () => {
        cy.visit(Cypress.env('purchase_confirmation_url'))
    })

    it('visit WC purchase confirmation page with nodedupe parameter', () => {
        cy.visit(Cypress.env('purchase_confirmation_url') + '&nodedupe')
    })

    it('visit WC purchase confirmation page twice, checking deduping', () => {
        cy.visit(Cypress.env('purchase_confirmation_url'))
        cy.wait(4000);
        cy.visit(Cypress.env('purchase_confirmation_url'))
    })

    it('visit WC purchase confirmation with conversion cart data turned off', () => {
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset_conversion_cart_data_off + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
        cy.visit(Cypress.env('purchase_confirmation_url'))
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    it('visit WC regular page', () => {
        cy.visit('/sample-page/')
    })

    it('visit WC shortcode test page', () => {
        cy.visit('/shortcode-test/')
    })

    // it('visit WC post page', () =>{
    //     cy.visit('/2019/12/13/hello-world/')
    // })

    it('visit WC 404 page', () => {
        cy.visit('/abcd/', {failOnStatusCode: false})
    })

    it('visit login page', () => {
        cy.visit('/wp-admin/')
    })
})
