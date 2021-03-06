describe('check minimum dev requirements', () => {

    const wgact_options_preset = Cypress.env('wgact_options_preset');

    // seed options into database
    before(function (){
        // save current options to tmp file
        cy.exec('wp option get wgact_plugin_options --format=json --path=' + Cypress.env('wordpress_install_directory') + ' > ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json').its('code').should('eq', 0)

        // load preset
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    after(function (){
        // load from before test run
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json' + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    beforeEach(function() {
        cy.visit('/wp-login.php')

        cy.wait(200);
        cy.get('#user_login').type( 'a{rightarrow}d{rightarrow}m{rightarrow}i{rightarrow}n{rightarrow}', {delay: 100} );
        cy.get('#user_pass').type( Cypress.env('admin_password'), {delay: 100} );
        cy.get('#wp-submit').click();
    })

    afterEach(() =>{
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
        })
        cy.contains('Warning')
            .should('not.exist')
    })

    it('visit WooCommerce admin dashboard page', () => {
        cy.visit('/wp-admin/admin.php?page=wc-admin')

    })

    it('visit WooCommerce status page and check if PHP 7.2 is active', () => {
        cy.visit('/wp-admin/admin.php?page=wc-status')
        cy.get('td[data-export-label="PHP Version"] + td + td')
            .contains(/^7.2/)
    })

    it('visit woopt Pixel settings page', () => {
        cy.visit('/wp-admin/admin.php?page=wgact')
    })

    // it('log out', () => {
    //     cy.contains('Log Out').click({ force: true })
    // })
    
})
