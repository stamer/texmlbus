ALTER TABLE `retval_jats` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_pagelimit` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_pdf` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_pdf_edge` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_xhtml` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_xhtml_edge` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_xml` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `retval_xml_edge` CHANGE `prev_retval` `prev_retval` ENUM('unknown','not_qualified','missing_errlog','timeout','fatal_error','missing_macros','missing_figure','missing_bib','missing_file','error','warning','no_problems','ok_exitcrash','rerun_unknown','rerun_not_qualified','rerun_missing_errlog','rerun_timeout','rerun_fatal_error','rerun_missing_macros','rerun_missing_figure','rerun_missing_bib','retun_missing_file','rerun_error','rerun_warning','rerun_no_problems','rerun_ok_exitcrash') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
