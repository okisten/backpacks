;
if (typeof Ciklum == "undefined") {
    Ciklum = {};
}
if (typeof Ciklum.ProductGrid == "undefined") {
    Ciklum.ProductGrid = {};
}

$j(function(){
        /* Data editor*/
        Ciklum.ProductGrid.editProductsInList = function() {
            $j('.productgrid-list-save, .productgrid-list-reset, .productgrid-list-cancel').show();
            $j('.productgrid_list_edit').hide();
            $j('.editable-field').show();
            $j('.original-field').hide();
        }
        Ciklum.ProductGrid.cancelProductsInList = function() {
            $j('.productgrid-list-save, .productgrid-list-reset, .productgrid-list-cancel').hide();
            $j('.productgrid_list_edit').show();
            $j('.editable-field').hide();
            $j('.original-field').show();
        }

        Ciklum.ProductGrid.resetProductsInList = function() {
            $j('.js-editable-changed').each(function() {
                var $element = $j(this);
                $element.val($element.data('original')).removeClass('js-editable-changed');
            })
        }

        Ciklum.ProductGrid.saveProductsInList = function(url) {
            $j('body').append('<form name="productgrid_pricemaster_form" method="POST" id="productgrid_pricemaster_form" action = "' + url + '" ></form>');
            $form = $j('#productgrid_pricemaster_form');
            
            $j('.js-editable-changed').each(function(){
                $element  = $j(this);
                $form.append('<input type="hidden" name="'+$element.attr('name')+'" value="'+$element.val()+'" >')
            })
            $form.submit();
        }


        /* Highlight selected*/
        $j('#productGrid').on('change', '.editable-field', function() {
            var $element = $j(this);
            if ($element.val() != $element.data('original')) {
                $element.addClass('js-editable-changed');
            } else {
                $element.removeClass('js-editable-changed');
            }
        })


        /* Key managment */
        $j('#productGrid').on('keypress', 'input.editable-field', function(e) {
            var groupe = $j(this).data('editable-groupe');
            var code = e.keyCode || e.which;
            if (code == 13 || code == 40) {
                var focusElement = $j(this).closest('tr').next().find('.editable-groupe-' + groupe);
                if (focusElement.length) {
                    focusElement.focus().select();
                } else {
                    $j('input.editable-groupe-' + groupe).eq(0).select();
                }
                return false
            }

            if (code == 38) {
                var focusElement = $j(this).closest('tr').prev().find('.editable-groupe-' + groupe);
                if (focusElement.length) {
                    focusElement.focus().select();
                } else {
                    $j('input.editable-groupe-' + groupe).focus().select();
                }
                return false
            }
        })
        $j('#productGrid').on('focus', 'input.editable-field', function(e) {
            this.setSelectionRange(0, this.value.length)
        });

        /* Add/remove default grid column */
        $j('#js-productgrid-apply-show-hide-fields .hidefields').on('change', function() {
            $j('#js-productgrid-apply-show-hide-fields').submit();
        })

        /* Add/remove additional grid column */

        $j('#js-productgrid-add-attr-to-grid li').on('click', function() {
            var code = $j(this).data('code');
            $form = $j('#js-productgrid-apply-show-hide-fields');
            $form.append('<input type="hidden" name="additionalfield_add" value="'+code+'" >');
            $form.submit();
        })

        $j('#js-productgrid-remove-attr-from-grid li').on('click', function() {
            var code = $j(this).data('code');
            $form = $j('#js-productgrid-apply-show-hide-fields');
            $form.append('<input type="hidden" name="additionalfield_remove" value="'+code+'" >');
            $form.submit();
        })

        /**/
        Ciklum.ProductGrid.toggleAdditionalFields = function(){
            $j('#js-productgrid-collumn-managment').toggle();
        }

})
