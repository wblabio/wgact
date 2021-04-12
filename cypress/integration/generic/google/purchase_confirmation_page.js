describe('Google purchase confirmation page all gtag events', () => {

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

    it('visit WC purchase confirmation page with nodedupe parameter', () => {

        const gtag = cy.stub().as('gtag')

        // cy.on('window:before:load', (win) => {
        //     Object.defineProperty(win, 'gtag', {
        //         // configurable: false,
        //         get: () => gtag,
        //         set: () => {},
        //     })
        // })

        cy.visit(Cypress.env('purchase_confirmation_url') + '&nodedupe')



        // cy.get('@gtag').should('be.called')
        // cy.get('@gtag').should('be.calledWith', 'config', 'AW-965183221')
        // cy.get('@gtag').should('be.calledWith', 'config', 'UA-39746956-9')
        // cy.get('@gtag').should('be.calledWith', 'config', 'G-YQBXCRGVLT')

        // cy.get('@gtag').should('be.calledWith', 'event', 'purchase')
        // cy.get('@gtag').should('be.calledWith', 'event', 'purchase', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
    })
})
