<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    '@dateAddCol:= CASE WHEN last_recurring_date THEN last_recurring_date ELSE date END',
    '@rType:= CASE WHEN custom_recurring = 0 THEN \'month\' ELSE recurring_type END',
    '@rec:= CAST(recurring AS UNSIGNED)',
    'number',
    'total',
    'YEAR(date) as year',
    get_sql_select_client_company(),
    'recurring', // Frequncy
    'CASE WHEN cycles != 0 THEN cycles - total_cycles ELSE null end as cycles_remaining', // Cycles Passed
    '(SELECT date FROM tblinvoices t WHERE is_recurring_from=tblinvoices.id ORDER BY id DESC LIMIT 1) as last_date', // Last Date
    // Used only for filtering, in most case php and mysql timezone won't be the same and this may lead to incorect showing dates
    // However, the correct date will be calculated with php when the row is added into the table, see below
    'CASE WHEN (cycles > 0 AND cycles = total_cycles) THEN NULL
        WHEN @rType = "month" THEN DATE_ADD(@dateAddCol, INTERVAL @rec MONTH)
        WHEN @rType = "day" THEN DATE_ADD(@dateAddCol, INTERVAL @rec DAY)
        WHEN @rType = "week" THEN DATE_ADD(@dateAddCol, INTERVAL @rec WEEK)
        WHEN @rType = "year" THEN DATE_ADD(@dateAddCol, INTERVAL @rec YEAR)
        END as next_date', // Next Date
];

$sIndexColumn = 'id';
$sTable       = 'tblinvoices';

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = tblinvoices.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblinvoices.currency',
];

$where  = ['AND recurring != 0'];
$filter = [];

$agents    = $this->ci->invoices_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}

if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}


$years     = $this->ci->invoices_model->get_invoices_years();
$yearArray = [];

foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearArray, $year['year']);
    }
}

if (count($yearArray) > 0) {
    array_push($where, 'AND YEAR(date) IN (' . implode(', ', $yearArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('invoices', '', 'view')) {
    $userWhere = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblinvoices.id',
    'tblinvoices.clientid',
    'custom_recurring',
    'recurring_type',
    'cycles',
    'total_cycles',
    'symbol',
    'hash',
    'deleted_customer_name',
    // next recurring date
    'CASE WHEN last_recurring_date THEN last_recurring_date ELSE date end as helper_next_date',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

// Fix for sorting next_date to be as last when worting descending
$order = $this->ci->input->post('order');

if ($order && $order[0]['column'] == '10' && $order[0]['dir'] == 'asc') {
    usort($rResult, function ($a, $b) {
        if (is_null($a['next_date']) && !is_null($b['next_date'])) {
            return 1;
        }

        if (is_null($b['next_date']) && !is_null($a['next_date'])) {
            return -1;
        }
        // No sort if not null
        return 0;
    });
} elseif ($order && $order[0]['column'] == '10' && $order[0]['dir'] == 'desc') {
    usort($rResult, function ($a, $b) {
        if (!is_null($a['next_date']) && is_null($b['next_date'])) {
            return 1;
        }

        if (!is_null($b['next_date']) && is_null($a['next_date'])) {
            return -1;
        }
        // No sort if not null
        return 0;
    });
}
foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '';
    $row[] = '';
    $row[] = '';

    $numberOutput = '';

    $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_invoice(' . $aRow['id'] . '); return false;">' . format_invoice_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';

    if (has_permission('invoices', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = format_money($aRow['total'], $aRow['symbol']);

    $row[] = $aRow['year'];

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    $frequency = '';
    if ($aRow['custom_recurring'] == 0) {
        $frequency = _l('invoice_add_edit_recurring_month' . ($aRow['recurring'] > 1 ? 's' : ''), $aRow['recurring']);
    } else {
        if ($aRow['recurring_type'] == 'day') {
            $frequency = _l('frequency_every', $aRow['custom_recurring'] . ' ' . _l('invoice_recurring_days'));
        } elseif ($aRow['recurring_type'] == 'week') {
            $frequency = _l('frequency_every', $aRow['custom_recurring'] . ' ' . _l('invoice_recurring_weeks'));
        } elseif ($aRow['recurring_type'] == 'month') {
            $frequency = _l('frequency_every', $aRow['custom_recurring'] . ' ' . _l('invoice_recurring_months'));
        } elseif ($aRow['recurring_type'] == 'year') {
            $frequency = _l('frequency_every', $aRow['custom_recurring'] . ' ' . _l('invoice_recurring_years'));
        }
    }
    $row[] = $frequency;

    $row[] = $aRow['cycles_remaining'] == null ? _l('cycles_infinity') : $aRow['cycles_remaining'];

    $row[] = $aRow['last_date'] ? _d($aRow['last_date']) : '-';

    $compareRecurring = $aRow['recurring_type'];

    if ($aRow['custom_recurring'] == 0) {
        $compareRecurring = 'month';
    }

    $next_date = date('Y-m-d', strtotime('+' . $aRow['recurring'] . ' ' . strtoupper($compareRecurring), strtotime($aRow['helper_next_date'])));

    if ($aRow['cycles'] == 0 || $aRow['cycles'] != $aRow['total_cycles']) {
        $row[] = _d($next_date);
    } elseif ($aRow['cycles'] > 0 && $aRow['cycles'] == $aRow['total_cycles']) {
        $row[] = '<span class="badge">' . _l('recurring_has_ended', _l('invoice_lowercase')) . '</span>';
    } else {
        $row[] = '-';
    }

    $output['aaData'][] = $row;
}
