describe('check minimum dev requirements', () => {

    const wgact_options_preset = 'GA-standard-ecommerce.json';

    // seed options into database
    before(function (){
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    // test for errors in HTML output
    afterEach(() => {
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
        })
        cy.contains('Warning')
            .should('not.exist')

        cy.contains('Undefined')
            .should('not.exist')
    })

    it('visit WC purchase confirmation page', () =>{
        cy.visit(Cypress.env('purchase_confirmation_url'))
    })

})
