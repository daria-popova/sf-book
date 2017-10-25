<?php

namespace BookBundle\Form;

use Symfony\Component\Form\AbstractType;
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
            ->add('title')
            ->add('author')
            ->add('readDate')
            ->add('isDownloadAllowed')
            ->add('cover', FileType::class, ['required' => false])
            ->add('source', FileType::class, ['required' => false])
            ->add('save', SubmitType::class);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if ($data->getCover()) {
                $form->add('deleteCover', CheckboxType::class, ['mapped' => false, 'required' => false]);
            }

            if ($data->getSource()) {
                $form->add('deleteSource', CheckboxType::class, ['mapped' => false, 'required' => false]);
            }
        });
    }
}
