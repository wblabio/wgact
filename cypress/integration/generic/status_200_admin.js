// describe('status 200 admin', () => {

//     before(function() {
//         cy.visit('/wp-login.php')

//         cy.get('#user_login').type( 'a{rightarrow}d{rightarrow}m{rightarrow}i{rightarrow}n{rightarrow}', {delay: 100} );
//         cy.get('#user_pass').type( Cypress.env('admin_password'), {delay: 100} );
//         cy.get('#wp-submit').click();
//     })

//     afterEach(() =>{
//         cy.get('html').should(($html) => {
//             expect($html).to.not.contain('Fatal error')
//         })
//         cy.contains('Warning')
//             .should('not.exist')
//     })

//     // the following test won't work if we are running on an old version of PHP which triggers a warning on the dashboard
//     // it('visit WordPress admin dashboard page', () =>{
//     //     cy.visit('/wp-admin/')
//     // })

//     it('visit WooCommerce admin dashboard page', () =>{
//         cy.visit('/wp-admin/admin.php?page=wc-admin')
//     })

//     it('log out', () => {
//         cy.contains('Log Out').click({ force: true })
//     })


// })