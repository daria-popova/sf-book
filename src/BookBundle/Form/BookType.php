<?php

namespace BookBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                ['label' => 'form.title']
            )
            ->add(
                'author',
                TextType::class,
                ['label' => 'form.author']
            )
            ->add(
                'readDate',
                DateType::class,
                ['label' => 'form.readDate']
            )
            ->add(
                'isDownloadAllowed',
                CheckboxType::class,
                ['required' => false, 'label' => 'form.isDownloadAllowed']
            )
            ->add(
                'cover',
                FileType::class,
                ['required' => false, 'label' => 'form.cover']
            )
            ->add(
                'source',
                FileType::class,
                ['required' => false, 'label' => 'form.source']
            )
            ->add(
                'save',
                SubmitType::class,
                ['label' => 'form.save']
            );

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if ($data->getCover()) {
                $form->add(
                    'deleteCover',
                    CheckboxType::class,
                    [
                        'mapped' => false,
                        'required' => false,
                        'label' => 'form.deleteCover'
                    ]
                );
            }

            if ($data->getSource()) {
                $form->add(
                    'deleteSource',
                    CheckboxType::class,
                    [
                        'mapped' => false,
                        'required' => false,
                        'label' => 'form.deleteSource'
                    ]
                );
            }
        });
    }
}
