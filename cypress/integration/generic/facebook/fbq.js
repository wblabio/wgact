describe('Facebook fbq events', () => {

    const wgact_options_preset = Cypress.env('wgact_options_preset');

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
    })

    it('fire fbq AddToCart on /shop/ page', () => {

        cy.visit('/shop/')

        cy.window().then((win) => {

            cy.spy(win, 'fbq').as('fbq');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()

            cy.get('@fbq').should('be.calledOnce')
            cy.get('@fbq').should('be.calledOnceWith', 'track', 'AddToCart')

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')

            // cy.get('@gtag').should(($gtag) => {
            //     expect($gtag).to.have.been.calledWith('event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
            // })

            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })
})
