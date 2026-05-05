<?php

namespace App\Controllers;

use App\Models\RequestBukaNilaiModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $helpers = ['form', 'url'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function guardGradeWriteAccess(?array $tahunAjaran, string $lockedMessage, ?int $idKelas = null, ?int $idMapel = null)
    {
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tahun ajaran tidak ditemukan.');
        }

        $isLockedOrInactive = (($tahunAjaran['status_pengisian'] ?? null) === 'Kunci')
            || (($tahunAjaran['aktif'] ?? null) !== 'aktif');

        if (!$isLockedOrInactive) {
            return null;
        }

        if ($idMapel === null || $idMapel <= 0) {
            return redirect()->back()->with('error', $lockedMessage);
        }

        $idGuru = (int) session()->get('id_user');
        if ($idGuru > 0) {
            $requestModel = new RequestBukaNilaiModel();
            if ($requestModel->hasActiveAccess($idGuru, (int) $tahunAjaran['id_tahun_ajaran'], $idKelas, $idMapel)) {
                return null;
            }
        }

        return redirect()->back()->with('error', $lockedMessage);
    }

    protected function mapelBelongsToClass(int $idKelas, int $idMapel): bool
    {
        if ($idKelas <= 0 || $idMapel <= 0) {
            return false;
        }

        return \Config\Database::connect()->table('mapel_kelas')
            ->where('id_kelas', $idKelas)
            ->where('id_mapel', $idMapel)
            ->countAllResults() > 0;
    }

    protected function rejectIfMapelNotInClass(int $idKelas, int $idMapel)
    {
        if (!$this->mapelBelongsToClass($idKelas, $idMapel)) {
            return redirect()->back()->with('error', 'Mata pelajaran yang dipilih tidak berjalan pada kelas tersebut. Pilih mapel sesuai kelas.');
        }

        return null;
    }
}
