<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_leads extends App_import
{
    protected $notImportableFields = [];

    protected $requiredFields = ['name'];

    public function __construct()
    {
        $this->notImportableFields = do_action('not_importable_leads_fields', ['id', 'source', 'assigned', 'status', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash']);

        $this->addImportGuidelinesInfo('Duplicate email rows won\'t be imported.', true);

        parent::__construct();
    }

    public function perform()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);

                if ($databaseFields[$i] == 'name' && empty($row[$i])) {
                    $row[$i] = '/';
                } elseif ($databaseFields[$i] == 'country') {
                    $row[$i] = $this->countryValue($row[$i]);
                }

                $insert[$databaseFields[$i]] = $row[$i];
            }

            $insert = $this->trimInsertValues($insert);

            if (count($insert) > 0) {
                if ($this->isDuplicateLead($insert)) {
                    continue;
                }

                $this->incrementImported();

                $id = null;

                if (!$this->isSimulation()) {
                    $insert['dateadded'] = date('Y-m-d H:i:s');
                    $insert['addedfrom'] = get_staff_user_id();
                    $insert['status']    = $this->ci->input->post('status');
                    $insert['source']    = $this->ci->input->post('source');

                    if ($this->ci->input->post('responsible')) {
                        $insert['assigned'] = $this->ci->input->post('responsible');
                    }

                    $tags = '';
                    if (isset($insert['tags'])) {
                        $tags = $insert['tags'];
                        unset($insert['tags']);
                    }

                    $this->ci->db->insert('tblleads', $insert);
                    $id = $this->ci->db->insert_id();

                    if ($id) {
                        handle_tags_save($tags, $id, 'lead');
                    }
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                }

                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'leads');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    protected function tags_formatSampleData()
    {
        return 'tag1,tag2';
    }

    public function formatFieldNameForHeading($field)
    {
        if (strtolower($field) == 'title') {
            return 'Position';
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function failureRedirectURL()
    {
        return admin_url('leads/import');
    }

    private function leadEmailExists($email)
    {
        return total_rows('tblleads', ['email' => $email]) > 0;
    }

    private function isDuplicateLead($data)
    {
        if (isset($data['email']) && $data['email'] != '') {
            if ($this->leadEmailExists($data['email'])) {
                return true;
            }
        }

        return false;
    }

    private function formatValuesForSimulation($values)
    {
        foreach ($values as $column => $val) {
            if ($column == 'country' && !empty($val) && is_numeric($val)) {
                $country = $this->getCountry(null, $val);
                if ($country) {
                    $values[$column] = $country->short_name;
                }
            }
        }

        return $values;
    }

    private function getCountry($search = null, $id = null)
    {
        if ($search) {
            $this->ci->db->where('iso2', $search);
            $this->ci->db->or_where('short_name', $search);
            $this->ci->db->or_where('long_name', $search);
        } else {
            $this->ci->db->where('country_id', $id);
        }

        return  $this->ci->db->get('tblcountries')->row();
    }

    private function countryValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $country = $this->getCountry($value);
                $value   = $country ? $country->country_id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }
}
