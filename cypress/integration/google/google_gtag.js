describe('status 200 public', () => {

    // Cypress.on('window:before:load', (win) => {
    //     // because this is called before any scripts
    //     // have loaded - the ga function is undefined
    //     // so we need to create it.
    //     win.gtag = cy.spy().as('gtag')
    // })

    const wgact_options_preset = 'all-pixels-enabled.json';
    // const wgact_options_preset_conversion_cart_data_off     = 'all-pixels-enabled_conversion-cart-data-off.json';
    // const wgact_options_preset_cookie_consent_fully_enabled = 'all-pixels-enabled_cookie-consent-fully-enabled.json';


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

        // expect(win.gtag).to.be.called

    })

    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__google-analytics/cypress/integration/ga-method-stubbing.js
    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__window-fetch/cypress/integration/spy-on-fetch-spec.js
    // it('fire gtag add_to_cart on /shop/ page', () => {
    //
    //     cy.visit('/shop/')
    //
    //     cy.window().then((win) => {
    //
    //         cy.spy(win, 'gtag').as('gtag');
    //
    //         // add to an item to the cart
    //         cy.get('.add_to_cart_button')
    //             .eq(0)
    //             .click()
    //
    //         // cy.get('@gtag').should('be.called')
    //         cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
    //     })
    // })

    // it('fire gtag remove_from_cart on /shop/ page', () => {
    //
    //     cy.visit('/shop/')
    //
    //     cy.window().then((win) => {
    //
    //         cy.spy(win, 'gtag').as('gtag');
    //
    //         // add to an item to the cart
    //         cy.get('.add_to_cart_button')
    //             .eq(0)
    //             .click()
    //
    //         // remove from cart
    //         cy.get('[id="site-header-cart"]')
    //             .trigger('mouseover')
    //             .wait(200)
    //
    //         cy.get('.woocommerce-mini-cart-item')
    //             .get('.remove_from_cart_button')
    //             .click({force:true})
    //             .wait(400)
    //
    //         // cy.get('@gtag').should('be.called')
    //         // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
    //         cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
    //     })
    // })

    // it('fire gtag add_to_cart on /product/album/ page', () => {
    //
    //     cy.visit('/product/album/')
    //
    //     cy.window().then((win) => {
    //
    //         cy.spy(win, 'gtag').as('gtag');
    //
    //         // add to an item to the cart
    //         cy.get('.single_add_to_cart_button')
    //             .eq(0)
    //             .click()
    //
    //         // cy.get('@gtag').should('be.called')
    //         cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
    //     })
    // })

    // it('fire gtag add_to_cart on /product/hoodie/ page', () => {
    //
    //     cy.visit('/product/hoodie/')
    //
    //     cy.window().then((win) => {
    //
    //         cy.spy(win, 'gtag').as('gtag');
    //
    //         cy.get('#pa_color')
    //             .select('Blue')
    //             .get('#logo')
    //             .select('Yes')
    //
    //         cy.contains('Add to cart')
    //             .click()
    //
    //         // cy.get('@gtag').should('be.called')
    //         cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
    //         // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
    //     })
    // })

    it('fire gtag add_to_cart on /product/logo-collection/ page', () => {

        cy.visit('/product/logo-collection/')

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add grouped product
            cy.get('.input-text.qty')
                .eq(0)
                .type('3')
                .get('.input-text.qty')
                .eq(1)
                .type('4')
                .get('.input-text.qty')
                .eq(2)
                .type('5')

            cy.contains('Add to cart')
                .click()

            // cy.get('@gtag').should('be.called')
            cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

})
