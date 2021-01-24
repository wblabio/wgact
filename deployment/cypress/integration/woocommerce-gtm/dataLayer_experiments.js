// describe('dataLayer experiments', () => {

//     it('get the dataLayer on a product page and test its contents', () =>{
//         cy.visit('/product/album/')
//             .wait(1000)

//         cy.window().then((win) => {
//             // debugger
//             assert.isDefined(win.dataLayer, 'Data Layer is defined');
//             // debugger
//             // let d = gtm['GTM-PV4MXFN'].dataLayer;
//             let d = win.google_tag_manager['GTM-PV4MXFN'].dataLayer;
//             // debugger
//             console.log(d);
//             console.log('typof: ' + typeof d.get('shop'));
//             let shop = d.get('shop');
//             // let shop = d['shop'];
//             console.log(shop);
//             let list = shop['list'];
//             // console.log(d.get('shop')['list']);
//             console.log(list);

//         })
//     })
// })