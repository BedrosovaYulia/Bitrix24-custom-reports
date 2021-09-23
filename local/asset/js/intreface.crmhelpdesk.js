$(function(){
    var tabName = 'tab_intreface_support';
    var $tab = $('#CRM_COMPANY_SHOW_V12_tab_' + tabName);
    var $tabContainer = $('#inner_tab_' + tabName);
    if($tab.length == 1 && $tabContainer.length == 1){
        try{
            var company_id = $('.bx-crm-view-menu')
                .attr('id')
                .split('_')
                .slice(-1)
                .pop()
                ;
            if(company_id <= 0){
                throw new Error('Company ID is incorrect');
            }

            // inject html for tab
            $tabContainer.find('div').html('<div class="bx-crm-view-fieldset"><div class="bx-crm-view-fieldset-content-tiny"><table class="bx-crm-view-fieldset-content-table"><tr><td class="bx-field-value" colspan="2"><div id="crm_company_show_v12_company_intreface_support_list_wrapper"></div></td></tr></table></div></div>');

            // inject lazy load
            setTimeout(function(){
                BX.CrmFormTabLazyLoader.create(
                    "crm_company_show_v12_company_intreface_support_list_wrapper",
                    {
                        containerID: "crm_company_show_v12_company_intreface_support_list_wrapper",
                        serviceUrl: "/local/templates/.default/handlers/intreface.crmhelpdesk/helpdesk.ticket.list/?company_id=" + company_id,
                        formID: "CRM_COMPANY_SHOW_V12",
                        tabID: tabName,
                        params: {}
                    }
                );
            }, 200);

            var entityCreateTicket = new BX.PopupWindow('crm_ticket_create_dialog_' + company_id, window.body, {
                autoHide: true,
                offsetTop: 0,
                offsetLeft: 0,
                lightShadow: true,
                closeIcon: true,
                closeByEsc: false,
                titleBar: true,
                draggable: true,
                overlay: {
                    backgroundColor: '#000', opacity: '80'
                },
                buttons: [
                    new BX.PopupWindowButton({
                        text: "Submit",
                        className: "popup-window-button-create",
                        events: {click: function(){
                            intreface_crmhelpdesk.dialog.create.submit();
                        }}
                    }),
                    new BX.PopupWindowButton({
                        text: "Cancel",
                        className: "popup-window-button-link popup-window-button-link-cancel",
                        events: {
                            click: function(){
                                this.popupWindow.close();
                            }
                        },
                        id: tabName + '-close-btn'
                    })
                ],
                events: {
                    onAfterPopupShow: function(){
                        var $select = $('[name="ticket[email]"]');
                        $select.empty();
                        $.each(intreface_crmhelpdesk.data.contacts, function(key, value){
                            $select
                                .append(
                                    $("<option></option>")
                                        .attr("value", key)
                                        .text(value)
                                )
                            ;
                        });
                    }
                }
            });
            entityCreateTicket.setContent(
                '<div data-block="notify"></div>'
                + '<form>'
                + '<input type="hidden" name="ticket[company_id]" value="' + company_id + '" class="content-edit-form-field-input-text">'
                + '<table class="content-edit-form">'
                + '<tr>'
                + '<td class="content-edit-form-field-name">Contact</td>'
                + '<td class="content-edit-form-field-input">'
                + '<select name="ticket[email]" class="content-edit-form-field-input-select">'
                + '</select>'
                + '</td>'
                + '</tr>'
                + '<tr>'
                + '<td class="content-edit-form-field-name">Title</td>'
                + '<td class="content-edit-form-field-input">'
                + '<input name="ticket[title]" class="content-edit-form-field-input-text">'
                + '</td>'
                + '</tr>'
                + '<tr>'
                + '<td class="content-edit-form-field-name">Description</td>'
                + '<td class="content-edit-form-field-input">'
                + '<textarea name="ticket[description]" class="content-edit-form-field-input-textarea"></textarea>'
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</form>'
            );
            entityCreateTicket.setTitleBar({content: BX.create("span", {html: '<b>Create a ticket</b>'})});

            var entityViewTicket = new BX.PopupWindow('crm_ticket_view_dialog_' + company_id, window.body, {
                className: 'crm_ticket_view_dialog',
                autoHide: true,
                offsetTop: 0,
                offsetLeft: 0,
                lightShadow: true,
                closeIcon: true,
                closeByEsc: false,
                titleBar: true,
                draggable: true,
                overlay: {
                    backgroundColor: '#000', opacity: '80'
                },
                buttons: [
                    /*new BX.PopupWindowButton({
                     text: "Submit",
                     className: "popup-window-button-create",
                     events: {click: function(){
                     intreface_crmhelpdesk.create.dialog.submit();
                     }}
                     }),*/
                    new BX.PopupWindowButton({
                        text: "Cancel",
                        className: "popup-window-button-link popup-window-button-link-cancel",
                        events: {
                            click: function(){
                                this.popupWindow.close();
                            }
                        },
                        id: tabName + '-close-btn'
                    })
                ],
                events: {
                    onAfterPopupShow: function(){

                    }
                }
            });

            window.intreface_crmhelpdesk = {
                company_id: company_id,
                data: {},
                ticket: {
                    view: function(id){
                        var $link = $('[data-ticket-id="' + id + '"]');
                        if($link.length == 1){
                            $link[0].click();
                        }
                    }
                },
                dialog: {
                    view: {
                        entity: entityViewTicket,
                        show: function(ticket_id){
                            var self = this;
                            $.get('/local/templates/.default/handlers/intreface.crmhelpdesk/helpdesk.ticket.message.list/?ticket_id=' + ticket_id, function(data){
                                self.entity.show();

                                var html =
                                    '<div data-block="notify"></div>'
                                    + '<form>'
                                    + '<input type="hidden" name="ticket[company_id]" value="' + company_id + '" class="content-edit-form-field-input-text">'
                                    + '<table class="content-edit-form">'
                                ;

                                if(data.success == 'Y'){
                                    for(var i in data.message){

                                        var messageCss = data.message[i].MESSAGE_BY_SUPPORT_TEAM == 'Y'
                                            ?'support-team'
                                            :'client'
                                        ;

                                        var author = '';
                                        if($.trim(data.message[i].CREATED_NAME) != ''){
                                            author = data.message[i].CREATED_NAME;
                                        }
                                        else{
                                            author = data.message[i].OWNER_SID;
                                        }
                                        if(data.message[i].OWNER_SID != null && data.message[i].OWNER_SID != data.message[i].CREATED_EMAIL){
                                            if(data.ticket.OWNER_USER_ID == 0){
                                                author = data.ticket.OWNER_SID + ' by ' + author;
                                            }
                                            else{
                                                author = data.ticket.OWNER_NAME + ' by ' + author;
                                            }
                                        }

                                        html += '<tr>'
                                            + '<td>'
                                            + '<div class="ticket-view-message ' + messageCss + '">'
                                            + '<div class="author"><span>' + author + '</span> on ' + data.message[i].DATE_CREATE + '</div>'
                                            + '<div class="message">' + data.message[i].MESSAGE.replace(/(?:\r\n|\r|\n)/g, '<br />') + '</div>'
                                            + '</div>'
                                            + '</td>'
                                            + '</tr>'
                                        ;
                                    }
                                }

                                html +=
                                    '</table>'
                                    + '</form>'
                                ;

                                self.entity.setContent(html);
                                self.entity.setTitleBar({content: BX.create("span", {html: '<b>View a ticket #' + ticket_id + '</b>'})});
                            }, 'json');

                            return false;
                        }
                    },
                    create: {
                        entity: entityCreateTicket,
                        show: function(){
                            this.entity.show();

                            return false;
                        },
                        submit: function(){
                            var $container = $('#crm_ticket_create_dialog_' + company_id);
                            if($container.length == 1){
                                var valid = true;
                                // Validate
                                $container.find(':input').each(function(){
                                    var $this = $(this);
                                    if($this.val() == ''){
                                        valid = false;
                                        $this.addClass('has-error');
                                    }
                                    else{
                                        $this.removeClass('has-error');
                                    }
                                });

                                if(valid === true){
                                    $.post('/local/templates/.default/handlers/intreface.crmhelpdesk/helpdesk.ticket.add/', $container.find('form').serialize(), function(response){
                                        if(response.success == 'Y'){
                                            $(":input:not([type=hidden])").val('');
                                            // Reload list
                                            bxGrid_crm_helpdesk_grid.Reload('/local/templates/.default/handlers/intreface.crmhelpdesk/helpdesk.ticket.list/?company_id=' + intreface_crmhelpdesk.company_id + '&by=id&order=desc');

                                            $('#' + tabName + '-close-btn').trigger('click');
                                            /*$container.find('[data-block="notify"]').text('Ticket #' + response.id + ' has been created.');*/
                                        }
                                        else{
                                            $container.find('[data-block="notify"]').text(response.error);
                                        }

                                        setTimeout(function(){
                                            $container.find('[data-block="notify"]').text('');
                                        }, 7000);
                                    }, 'json');
                                }
                            }
                        }
                    }
                }
            };
        }
        catch (e){
            console.log(e.message);
        }
    }
});