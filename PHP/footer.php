<?php
// Asumimos que las rutas base se definieron en header.php
$base_path = $base_path ?? '../';
$js_path = $js_path ?? $base_path . 'JavaScript/';
$html_path = $html_path ?? $base_path . 'HTML/';
$php_path = $php_path ?? $base_path . 'PHP/';
?>
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-logo-info">
                <img src="<?php echo $img_path; ?>UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
                <div class="footer-info">
                    <p>París 532, Haedo (1706)</p>
                    <p>Buenos Aires, Argentina</p>
                    <br>
                    <p>Número de teléfono del depto.</p>
                    <br>
                    <p>extension@frh.utn.edu.ar</p>
                </div>
            </div>
            <div class="footer-social-legal">
                <div class="footer-social">
                    <a href="https://www.youtube.com/@facultadregionalhaedo-utn3647" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/school/utn-facultad-regional-haedo/" target="_blank"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="footer-legal">
                    <a href="mailto:extension@frh.utn.edu.ar">Contacto</a>
                    <br> 
                    <a href="#politicas">Políticas de Privacidad</a>
                </div>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-nav">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.html">Validar</a></li>
                    <li><a href="<?php echo $html_path; ?>sobrenosotros.html">Sobre Nosotros</a></li>
                    <li><a href="<?php echo $html_path; ?>contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Alumno'; ?></h4>
                    <ul>
                        <?php if ($_SESSION['user_rol'] == 1): // Admin ?>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <?php else: // Alumno ?>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                        <?php endif; ?>
                    </ul>
                <?php else: ?>
                    <h4>Acceso</h4>
                    <ul>
                        <li><a href="<?php echo $php_path; ?>iniciosesion.php">Iniciar Sesión</a></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Scripts -->
    <script src="<?php echo $js_path; ?>general.js"></script>
    <?php if (isset($extra_scripts) && is_array($extra_scripts)): ?>
        <?php foreach ($extra_scripts as $script): ?>
            <script src="<?php echo $js_path . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
