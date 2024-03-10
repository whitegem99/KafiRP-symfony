(function ($) {
    "use strict";
    
    /*Default*/
    if( $('.data-table-default').length ) {
        $('.data-table-default').DataTable({
            responsive: true,
            pageLength: 100,
            order: [[1, 'asc']],
            language: {
                paginate: {
                    previous: '<i class="zmdi zmdi-chevron-left"></i>',
                    next:     '<i class="zmdi zmdi-chevron-right"></i>'
                },
                search: 'Arama: ',
                infoEmpty: 'Hiç kayıt yok',
                info: '_TOTAL_ kayıttan _START_-_END_ arası gösteriliyor',
                emptyTable: 'Hiç kayıt yok',
                loadingRecords: 'Yükleniyor...',
                processing: 'İşleniyor...',
                lengthMenu: '_MENU_ Kayıt Göster',
            }
        });
    }
    
    /*Export Buttons*/
    if( $('.data-table-export').length ) {
        $('.data-table-export').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                paginate: {
                    previous: '<i class="zmdi zmdi-chevron-left"></i>',
                    next:     '<i class="zmdi zmdi-chevron-right"></i>'
                }
            }
        });
    }
    
})(jQuery);