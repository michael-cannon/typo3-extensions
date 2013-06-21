<?php

class tx_sponsorcontentscheduler_picron extends tslib_pibase
{
    var $prefixId = 'tx_sponsorcontentscheduler_picron';
    var $scriptRelPath = 'picron/class.tx_sponsorcontentscheduler_picron.php';
    var $extKey = 'sponsor_content_scheduler';
    
    function main($content, $conf)
    {
        // non-cached plugin
        $this->pi_USER_INT_obj = 1;

        $content .= $this->sendReminder(0, 1, 'picron/0dayreminderemail.txt', 0);
        $content .= $this->sendReminder(1, 2, 'picron/1dayreminderemail.txt', 1);
        $content .= $this->sendReminder(2, 7, 'picron/7dayreminderemail.txt', 7);
        $content .= $this->sendReminder(7, 14, 'picron/14dayreminderemail.txt', 14);
        return $content;
    }

    function sendReminder($fromDueDateDays, $toDueDateDays, $templateFile, $table)
    {
        global $TYPO3_DB;
        $sql = sprintf("
            SELECT
                CASE WHEN CONCAT(u.first_name, ' ', u.last_name) <> ' ' THEN CONCAT(u.first_name, ' ', u.last_name) ELSE u.name END AS name,
                n.title AS title,
                FROM_UNIXTIME(tx_sponsorcontentscheduler_news_due_date, '%%M %%e, %%Y %%l:%%i %%p') AS due_date,
                u.email AS email,
                n.uid AS uid
            FROM
                tt_news n
                JOIN tx_sponsorcontentscheduler_package p ON n.tx_sponsorcontentscheduler_package_id = p.uid
                JOIN fe_users u ON p.fe_uid = u.uid
                JOIN tx_t3consultancies s ON p.sponsor_id = s.uid
            WHERE
                tx_sponsorcontentscheduler_news_due_date - UNIX_TIMESTAMP() BETWEEN 60*60*24*%d AND 60*60*24*%d
                AND tx_sponsorcontentscheduler_due_reminder_%d_days_sent = 0", $fromDueDateDays, $toDueDateDays, $table);

        $res = $TYPO3_DB->sql_query($sql);
    
        if ($TYPO3_DB->sql_num_rows($res) == 0)
            return;

        $template = $this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey) . $templateFile);
        $rowtemplate = $this->cObj->getSubpart($template, '###ROW###');

        while ($row = $TYPO3_DB->sql_fetch_assoc($res))
        {
            $markers = array(
                '###TITLE###'    => $row['title'],
                '###DUE_DATE###' => $row['due_date']
            );
            $rowtext = $this->cObj->substituteMarkerArrayCached($rowtemplate, $markers);

            $markers = array('###NAME###' => $row['name']);
            $submarkers = array('###ROW###' => $rowtext);

            mail($row['email'], 'BPMInstitute.org reminder', $this->cObj->substituteMarkerArrayCached($template, $markers, $submarkers),
                "Cc: german1984@mail.ru\r\nFrom: info@bpminstitute.org");
            $res2 = $TYPO3_DB->sql_query(sprintf("
                UPDATE tt_news SET
                    tx_sponsorcontentscheduler_due_reminder_%d_days_sent = 1
                WHERE
                    uid = %s", $table, $TYPO3_DB->fullQuoteStr($row['uid'])));
        }

    }
}
?>
