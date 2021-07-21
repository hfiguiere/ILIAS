<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPortfolioCertificateFileService
{
    private ?Filesystem $filesystem;
    private ?ilLogger $logger;
    private const PERSISTENT_CERTIFICATES_DIRECTORY = 'PersistentCertificates/';
    private const CERTIFICATE_FILENAME = 'certificate.pdf';

    public function __construct(?Filesystem $filesystem = null, ?ilLogger $logger = null)
    {
        global $DIC;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->storage();
        }
        $this->filesystem = $filesystem;

        if (null === $logger) {
            $logger = $DIC->logger()->root();
        }
        $this->logger = $logger;
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @throws FileAlreadyExistsException
     * @throws IOException
     * @throws ilException
     */
    public function createCertificateFile(int $userId, int $objectId) : void
    {
        $userCertificateRepository = new ilUserCertificateRepository();

        $userCertificate = $userCertificateRepository->fetchActiveCertificate($userId, $objectId);

        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        if (false === $this->filesystem->hasDir($dirPath)) {
            $this->filesystem->createDir($dirPath);
        }

        $pdfGenerator = new ilPdfGenerator($userCertificateRepository, $this->logger);

        $pdfScalar = $pdfGenerator->generate($userCertificate->getId());

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;
        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }

        $this->filesystem->write($completePath, $pdfScalar);
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function deliverCertificate(int $userId, int $objectId) : void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId;
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;

        $completePath = $dirPath . '/' . $fileName;
        if ($this->filesystem->has($completePath)) {
            $userCertificateRepository = new ilUserCertificateRepository();

            $userCertificate = $userCertificateRepository->fetchActiveCertificateForPresentation($userId, $objectId);

            $downloadFilePath = CLIENT_DATA_DIR . '/' . $completePath;
            $delivery = new \ilFileDelivery($downloadFilePath);
            $delivery->setMimeType(\ilMimeTypeUtil::APPLICATION__PDF);
            $delivery->setConvertFileNameToAsci(true);
            $delivery->setDownloadFileName(\ilFileUtils::getValidFilename($userCertificate->getObjectTitle() . '.pdf'));

            $delivery->deliver();
        }
    }

    /**
     * @param int $userId
     * @throws IOException
     */
    public function deleteUserDirectory(int $userId) : void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        if (true === $this->filesystem->hasDir($dirPath)) {
            $this->filesystem->deleteDir($dirPath);
        }
    }

    /**
     * @param int $userId
     * @param int $objectId
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function deleteCertificateFile(int $userId, int $objectId) : void
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId;

        $completePath = $dirPath . '/' . $objectId . '_' . self::CERTIFICATE_FILENAME;

        if ($this->filesystem->has($completePath)) {
            $this->filesystem->delete($completePath);
        }
    }


    /**
     * @param int $userId
     * @param int $objectId
     * @return string
     * @throws ilException
     */
    public function createCertificateFilePath(int $userId, int $objectId) : string
    {
        $dirPath = self::PERSISTENT_CERTIFICATES_DIRECTORY . $userId . '/' . $objectId . '/';
        $fileName = $objectId . '_' . self::CERTIFICATE_FILENAME;
        $completePath = $dirPath . $fileName;
        if ($this->filesystem->has($completePath)) {
            return CLIENT_DATA_DIR . '/' . $completePath;
        }

        throw new ilException(sprintf('Certificate File does not exist in "%s"', $completePath));
    }
}
