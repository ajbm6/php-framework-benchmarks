<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */
 
namespace Doctrine\DBAL\Migrations\Tools\Console\Command;

use Symfony\Components\Console\Input\InputInterface,
    Symfony\Components\Console\Output\OutputInterface,
    Symfony\Components\Console\Input\InputArgument,
    Symfony\Components\Console\Input\InputOption,
    Doctrine\DBAL\Migrations\Migration,
    Doctrine\DBAL\Migrations\MigrationException,
    Doctrine\DBAL\Migrations\Configuration\Configuration,
    Doctrine\DBAL\Migrations\Configuration\YamlConfiguration,
    Doctrine\DBAL\Migrations\Configuration\XmlConfiguration;

/**
 * Command to view the status of a set of migrations.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class StatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('migrations:status')
            ->setDescription('View the status of a set of migrations.')
            ->addOption('configuration', null, InputOption::PARAMETER_OPTIONAL, 'The path to a migrations configuration file.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command outputs the status of a set of migrations:

    <info>%command.full_name%</info>
EOT
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->_getMigrationConfiguration($input, $output);

        $currentVersion = $configuration->getCurrentVersion();
        if ($currentVersion) {
            $currentVersionFormatted = $configuration->formatVersion($currentVersion) . ' (<comment>'.$currentVersion.'</comment>)';
        } else {
            $currentVersionFormatted = 0;
        }
        $latestVersion = $configuration->getLatestVersion();
        if ($latestVersion) {
            $latestVersionFormatted = $configuration->formatVersion($latestVersion) . ' (<comment>'.$latestVersion.'</comment>)';
        } else {
            $latestVersionFormatted = 0;
        }
        $executedMigrations = $configuration->getNumberOfExecutedMigrations();
        $availableMigrations = $configuration->getNumberOfAvailableMigrations();
        $newMigrations = $availableMigrations - $executedMigrations;

        $output->writeln("\n <info>==</info> Configuration\n");

        $info = array(
            'Name'                  => $configuration->getName() ? $configuration->getName() : 'Doctrine Database Migrations',
            'Configuration Source'  => $configuration instanceof \Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration ? $configuration->getFile() : 'manually configured',
            'Version Table Name'    => $configuration->getMigrationsTableName(),
            'Migrations Namespace'  => $configuration->getMigrationsNamespace(),
            'Migrations Directory'  => $configuration->getMigrationsDirectory(),
            'Current Version'       => $currentVersionFormatted,
            'Latest Version'        => $latestVersionFormatted,
            'Executed Migrations'   => $executedMigrations,
            'Available Migrations'  => $availableMigrations,
            'New Migrations'        => $newMigrations > 0 ? '<question>' . $newMigrations . '</question>' : $newMigrations
        );
        foreach ($info as $name => $value) {
            $output->writeln('    <comment>>></comment> ' . $name . ': ' . str_repeat(' ', 50 - strlen($name)) . $value);
        }

        if ($migrations = $configuration->getMigrations()) {
            $output->writeln("\n <info>==</info> Migration Versions\n");
            foreach ($migrations as $version) {
                $isMigrated = $version->isMigrated();
                $status = $isMigrated ? '<info>migrated</info>' : '<error>not migrated</error>';
                $output->writeln('    <comment>>></comment> ' . $configuration->formatVersion($version->getVersion()) . ' (<comment>' . $version->getVersion() . '</comment>)' . str_repeat(' ', 30 - strlen($name)) . $status);
            }
        }
    }
}