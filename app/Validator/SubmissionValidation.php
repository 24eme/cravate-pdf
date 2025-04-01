<?php

namespace Validator;

use Model\Submission;
use Validator\Validation;

class SubmissionValidation
{
    protected Submission $submission;
    protected Validation $validation;

    public function __construct(Submission $submission, Validation $validation)
    {
        $this->submission = $submission;
        $this->validation = $validation;
    }

    /**
     * @return Validation $validation
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Prends en paramètre des données (posté via formulaire par ex.) et les comparent
     * aux validateurs de la configuration de la procédure (form.php)
     *
     * @param array<string, mixed> $submissionDatas
     * @return bool
     */
    public function validate(array $submissionDatas)
    {
        $procedureValidators = $this->submission->procedure->getValidators();
        return $this->validation->validate($submissionDatas, $procedureValidators);
    }

    /**
     * Transforme les données dans un format attendu pour la génération du PDF
     * Supprime les entrées qui n'existent pas dans la configuration
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function formatData(array $data)
    {
        $formConfig = $this->submission->procedure->getConfigItem('form');
        $disabledFields = $this->submission->getDisabledFields();

        $data = $this->validation->formatData($formConfig, $data);
        $data = array_merge($data, $disabledFields);

        return $data;
    }

    public function check()
    {
        foreach ($this->submission->getForm() as $key => $field) {
            if (isset($field['required']) === false || $field['required'] === false) {
                continue;
            }

            if ($this->submission->getDatas($key) === null) {
                $this->validation->errors[] = ['field' => $key, 'message' => "Vous n'avez pas rempli le champs requis $key"];
            }
        }

        if ($this->submission->getAttachmentsConfig()) {
           if (empty($this->submission->getAttachments())) {
               $this->validation->errors[] = ['field' => 'ATTACHED_FILE', 'message' => "Vous n'avez pas soumis de pièce jointe"];
           } elseif (count($this->submission->getAttachmentsConfig()) !== count($this->submission->getAttachments())) {
               $this->validation->warnings[] = ['field' => 'ATTACHED_FILE', 'message' => "Toutes les pièces jointes n'ont pas été soumises"];
           }
        }
    }
}
