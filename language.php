<?php /************************************************************/

////////////////////////////////////////////////////////////////////
// PMOS Help Desk
// -----------------------------------------------------------------
//
// License info can be found in license.txt.  You must leave this
// notice as is.
// 
// Application: PMOS Help Desk
//      Author: John Heathco
//         Web: http://www.h2desk.com/pmos
//
// Use this software at your own risk.  It is neither supported nor
// actively developed.
//
// If you are looking for a supported and developed help desk,
// please check out the h2desk at http://www.h2desk.com
//
// -----------------------------------------------------------------
////////////////////////////////////////////////////////////////////

$LANG = array( );

$LANG[fields_not_filled] = "Um ou mais campos s&atilde;o inv&aacute;lidos ou n&atilde;o foram preenchidos.";
$LANG[create_new_ticket] = "Criar Novo Ticket";
$LANG[ticket_created] = "Seu ticket foi criado. Um email foi remetido para voc&ecirc; contendo todas as informa&ccedil;&otilde;es do ticket. Se voc&ecirc; precisar ver o seu ticket ou adicionar arquivos ao mesmo, acesse: <a href=\\\"{\$HD_URL_TICKET_VIEW}?id=\$ticket&email={\$_POST[email]}\\\">\$ticket</a>";
$LANG[fill_in_form] = "Para criar um novo ticket de suporte, preencha o formul&aacute;rio.";
$LANG[required_field] = "* avisa que este &eacute; um campo requerido";
$LANG[field_name] = "Nome:";
$LANG[field_email] = "Email:";
$LANG[field_subject] = "Assunto:";
$LANG[field_message] = "Mensagem:";
$LANG[field_department] = "Departamento:";
$LANG[field_priority] = "Prioridade:";
$LANG[field_priority_low] = "Baixa";
$LANG[field_priority_medium] = "M&eacute;dia";
$LANG[field_priority_high] = "Alta";
$LANG[field_created_on] = "Criado(a) On:";
$LANG[field_attachments] = "Anexos:";
$LANG[field_file] = "Arquivo:";
$LANG[field_ticket_id] = "Ticket ID#:";
$LANG[field_date] = "Data:";
$LANG[field_cc] = "CC:";
$LANG[ticket_notify] = "Notifique quando meu ticket for respondido.";
$LANG[select_department] = "Por favor, selecione o departamento para o qual o ticket ser&aacute; atribu&iacute;do:";
$LANG[next_step] = "Pr&oacute;ximo &gt;&gt;";

$LANG[no_find_ticket] = "N&atilde;o consegui achar um ticket com este ID.  Voc&ecirc; pode <a href=\\\"{\$HD_URL_TICKET_LOST}?cmd=lost\\\">procurar</a> todos os seus tickets ou tente procurar por outro.";
$LANG[no_subject] = "Sem assunto";
$LANG[delete_post] = "Deletar Post";
$LANG[confirm_delete_post] = "Tem certeza que voc&ecirc; quer deletar este post?";
$LANG[posted_by] = "Postado por";
$LANG[specify_message] = "Voc&ecirc; precisa escrever algo em sua mensagem ( o assunto &eacute; opcional ).";
$LANG[viewing_ticket] = "Vendo o  Ticket";
$LANG[post_reply] = "Responder";
$LANG[attach_file] = "Anexar arquivo";
$LANG[close_ticket] = "Fechar Ticket";
$LANG[carbon_copy] = "C&oacute;pia Carbono";
$LANG[separate_by_space] = "(separe os emails por um espa&ccedil;o)";
$LANG[confirm_close_ticket] = "Tem certeza que quer fechar este ticket?";
$LANG[ticket_no_longer_open] = "This ticket is no longer open.  You can <a href=\\\"{\$HD_CURPAGE}?cmd=open&id={\$_GET[id]}&email={\$_GET[email]}\\\">re-open</a> it.";
$LANG[view_ticket_help] = "To view a ticket, please enter your email address and ticket ID.  If you forgot your ticket ID, you can use the <a href=\\\"{\$HD_URL_TICKET_LOST}?cmd=lost\\\">ticket lookup</a>.";
$LANG[printable] = "Print";

$LANG[no_ticket_address] = "Could not find any tickets associated with that email address.";
$LANG[retrieve_lost_ticket] = "Retrieve Lost Ticket";
$LANG[ticket_info_sent] = "Your ticket information has been successfully sent to your email address.";
$LANG[email_address_used] = "Please enter your email address you used when creating your ticket.  All tickets ID numbers will be sent to that email address.";
$LANG[retrieve_lookup_button] = "Lookup";

$LANG[knowledge_base] = "Knowledge Base";
$LANG[faq_browsing] = "Browsing";
$LANG[faq_main_category] = "Main Category";
$LANG[faq_parent_category] = "Parent Category";
$LANG[search_for] = "Search for:";
$LANG[faq_subcategories] = "subcategories";
$LANG[faq_no_description] = "No description";
$LANG[faq_entries] = "entries";
$LANG[faq_symptoms] = "SYMPTOMS";
$LANG[faq_no_symptoms] = "No symptoms.";
$LANG[faq_solution] = "SOLUTION";
$LANG[faq_no_solution] = "No solution.";
$LANG[faq_no_results] = "No results found for your search.";
$LANG[faq_categories] = "Back to categories";
$LANG[faq_search_button] = "Search";

$LANG[link_home] = "Home";
$LANG[link_view_ticket] = "View Ticket";
$LANG[link_lost_ticket] = "Lost Ticket?";
$LANG[link_faq] = "Knowledge Base";
$LANG[link_staff_login] = "Staff Login";

$LANG[survey] = "Survey Our Support";
$LANG[survey_header] = "Thank you for choosing to fill out this brief survey.  Your feedback will help us to better serve you and others in the future.  You can view the original ticket <a href=\\\"{\$HD_URL_TICKET_VIEW}?cmd=view&id={\$_GET[id]}&email={\$_GET[email]}\\\" target=\\\"_blank\\\">here</a>.  Please rate us in the following categories:";
$LANG[survey_poor] = "(poor)";
$LANG[survey_excellent] = "(excellent)";
$LANG[survey_comments] = "Comments:";
$LANG[survey_submit] = "Submit Survey";
$LANG[survey_thanks] = "Thank you for taking the time to fill out the survey!  Your feedback is much appreciated.";

$LANG[banned] = "This email address and/or IP address has been banned from the help desk.";

$LANG[other_tickets] = "Mande-me via email meus outros tickets";

/********************************************************** PHP */?>
