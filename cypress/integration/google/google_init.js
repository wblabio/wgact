describe('Google init gtag', () => {

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

    // https://github.com/cypress-io/cypress/issues/897
    it('check if GA UA and GA 4 have been loaded successfully', () => {
        cy.visit('/')
        cy.window().then((win) => {
            // cy.log(win.ga.getAll()[0].get('trackingId'))

            expect(win.ga.getAll()[0].get('trackingId')).to.equal('UA-39746956-9')
            expect(win.google_tag_manager['UA-39746956-9'].dataLayer.name).to.equal('dataLayer')
            expect(win.google_tag_manager['G-YQBXCRGVLT'].dataLayer.name).to.equal('dataLayer')

            // expect(win.google_tag_manager['UA-39746956-9']).should('exist')
            // cy.window().should('have.property', 'google_tag_manager[\'UA-39746956-9\']')
        })
    })
})
