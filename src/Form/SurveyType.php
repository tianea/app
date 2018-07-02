<?php
/**
 * Survey form.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SurveyType.
 */
class SurveyType extends AbstractType
{
    /**
     * BuildForm function.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.name',
                'required' => true,
                'attr' => [
                    'max_length' => 180,
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            'min' => 3,
                            'max' => 180,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.description',
                'required' => false,
                'attr' => [
                    'max_length' => 1500,
                ],
            ]
        );
    }

    /**
     * GetBlockPrefix function.
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'survey_type';
    }
}
