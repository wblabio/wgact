describe('check minimum dev requirements', () => {

    before(function() {
        cy.visit('/wp-login.php')

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
        //     .containins('7.2')
    })

    it('log out', () => {
        cy.contains('Log Out').click({ force: true })
    })
    
})
