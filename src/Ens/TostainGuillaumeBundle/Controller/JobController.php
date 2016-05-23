<?php

namespace Ens\TostainGuillaumeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ens\TostainGuillaumeBundle\Entity\Job;
use Ens\TostainGuillaumeBundle\Form\JobType;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{
    /**
     * Lists all Job entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('EnsTostainGuillaumeBundle:Category')->getWithJobs();

        foreach($categories as $category)
        {
            $category->setActiveJobs($em->getRepository('EnsTostainGuillaumeBundle:Job')->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage')));
            $category->setMoreJobs($em->getRepository('EnsTostainGuillaumeBundle:Job')->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
        }

        return $this->render('EnsTostainGuillaumeBundle:job:index.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * Creates a new Job entity.
     *
     */
    public function newAction(Request $request)
    {
        $job = new Job();
        $job->setType('full-time');
        $form = $this->createForm('Ens\TostainGuillaumeBundle\Form\JobType', $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($job);
            $em->flush();

            return $this->redirect($this->generateUrl('job_preview', array(
                'company' => $job->getCompanySlug(),
                'location' => $job->getLocationSlug(),
                'token' => $job->getToken(),
                'position' => $job->getPositionSlug()
            )));
        }

        return $this->render('EnsTostainGuillaumeBundle:Job:new.html.twig', array(
            'entity' => $job,
            'form'   => $form->createView()
        ));

    }

    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction(Job $job)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->getActiveJob($job);

        $deleteForm = $this->createDeleteForm($job);

        return $this->render('EnsTostainGuillaumeBundle:job:show.html.twig', array(
            'job' => $job,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($token)
    {

        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

        if (!$job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        if ($job->getIsActivated()) {
            throw $this->createNotFoundException('Job is activated and cannot be edited.');
        }

        $editForm = $this->createForm(new JobType(), $job);
        $deleteForm = $this->createDeleteForm($token);

        return $this->render('EnsTostainGuillaumeBundle:job:edit.html.twig', array(
            'job' => $job,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function updateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

        if (!$job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $editForm   = $this->createForm(new JobType(), $job);
        $deleteForm = $this->createDeleteForm($token);

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($job);
            $em->flush();

            return $this->redirect($this->generateUrl('job_preview', array(
                'company' => $job->getCompanySlug(),
                'location' => $job->getLocationSlug(),
                'token' => $job->getToken(),
                'position' => $job->getPositionSlug()
            )));
        }

        return $this->render('EnsTostainGuillaumeBundle:Job:edit.html.twig', array(
            'entity'      => $job,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

            if (!$job) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }
            $em->remove($job);
            $em->flush();
        }

        return $this->redirectToRoute('job_index');
    }

    /**
     * Creates a form to delete a Job entity.
     *
     * @param Job $job The Job entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
            ;
    }

    public function previewAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

        if (!$job) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $deleteForm = $this->createDeleteForm($job->getId());
        $publishForm = $this->createPublishForm($job->getToken());
        $extendForm = $this->createExtendForm($job->getToken());

        return $this->render('EnsTostainGuillaumeBundle:job:show.html.twig', array(
            'job'      => $job,
            'delete_form' => $deleteForm->createView(),
            'publish_form' => $publishForm->createView(),
            'extend_form' => $extendForm->createView()
        ));
    }

    public function publishAction(Request $request, $token)
    {
        $form = $this->createPublishForm($token);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

            if (!$job) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $job->publish();
            $em->persist($job);
            $em->flush();

            $this->get('session')->getFlashBag()->set('notice', 'Your job is now online for 30 days.');
        }

        return $this->redirect($this->generateUrl('job_preview', array(
            'company' => $job->getCompanySlug(),
            'location' => $job->getLocationSlug(),
            'token' => $job->getToken(),
            'position' => $job->getPositionSlug()
        )));
    }

    private function createPublishForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
            ;
    }

    public function extendAction(Request $request, $token)
    {
        $form = $this->createExtendForm($token);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $job = $em->getRepository('EnsTostainGuillaumeBundle:Job')->findOneByToken($token);

            if (!$job) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            if (!$job->extend()) {
                throw $this->createNotFoundException('Unable to find extend the Job.');
            }

            $em->persist($job);
            $em->flush();

            $this->get('session')->getFlashBag()->set('notice', sprintf('Your job validity has been extended until %s.', $job->getExpiresAt()->format('m/d/Y')));
        }

        return $this->redirect($this->generateUrl('job_preview', array(
            'company' => $job->getCompanySlug(),
            'location' => $job->getLocationSlug(),
            'token' => $job->getToken(),
            'position' => $job->getPositionSlug()
        )));
    }

    private function createExtendForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
            ;
    }

}
