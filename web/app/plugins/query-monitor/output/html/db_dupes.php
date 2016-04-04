<?php
/*
Copyright 2009-2015 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class QM_Output_Html_DB_Dupes extends QM_Output_Html {

	public function __construct( QM_Collector $collector ) {
		parent::__construct( $collector );
		add_filter( 'qm/output/menus', array( $this, 'admin_menu' ), 45 );
	}

	public function output() {

		$data = $this->collector->get_data();

		if ( empty( $data['dupes'] ) ) {
			return;
		}

		$colspan = empty( $data['dupe_components'] ) ? 4 : 5;

		echo '<div class="qm" id="' . esc_attr( $this->collector->id() ) . '">';
		echo '<table cellspacing="0">';
		echo '<thead>';
		echo '<tr>';
		echo '<th colspan="' . absint( $colspan ) . '">' . esc_html( $this->collector->name() ) . '</th>';
		echo '</tr>';

		echo '<tr>';
		echo '<th>' . esc_html__( 'Query', 'query-monitor' ) . '</th>';
		echo '<th class="qm-num">' . esc_html__( 'Count', 'query-monitor' ) . '</th>';
		echo '<th>' . esc_html__( 'Callers', 'query-monitor' ) . '</th>';
		if ( ! empty( $data['dupe_components'] ) ) {
			echo '<th>' . esc_html__( 'Components', 'query-monitor' ) . '</th>';
		}
		echo '<th>' . esc_html__( 'Potential Troublemakers', 'query-monitor' ) . '</th>';
		echo '</tr>';

		echo '</thead>';

		echo '<tbody>';

		foreach ( $data['dupes'] as $sql => $queries ) {
			echo '<tr>';
			echo '<td>';
			echo self::format_sql( $sql ); // WPCS: XSS ok;
			echo '</td>';
			echo '<td class="qm-num">';
			echo esc_html( number_format_i18n( count( $queries ), 0 ) );
			echo '</td>';
			echo '<td class="qm-nowrap qm-ltr">';
			foreach ( $data['dupe_callers'][ $sql ] as $caller => $calls ) {
				printf(
					'<a href="#" class="qm-filter-trigger" data-qm-target="db_queries-wpdb" data-qm-filter="caller" data-qm-value="%s">%s</a><br><span class="qm-info">&nbsp;%s</span><br>',
					esc_attr( $caller ),
					esc_html( $caller ),
					esc_html( sprintf(
						_n( '%s call', '%s calls', $calls, 'query-monitor' ),
						number_format_i18n( $calls )
					) )
				);
			}
			echo '</td>';
			if ( isset( $data['dupe_components'][ $sql ] ) ) {
				echo '<td class="qm-nowrap">';
				foreach ( $data['dupe_components'][ $sql ] as $component => $calls ) {
					printf(
						'%s<br><span class="qm-info">&nbsp;%s</span><br>',
						esc_html( $component ),
						esc_html( sprintf(
							_n( '%s call', '%s calls', $calls, 'query-monitor' ),
							number_format_i18n( $calls )
						) )
					);
				}
				echo '</td>';
			}
			echo '<td class="qm-nowrap qm-ltr">';
			foreach ( $data['dupe_sources'][ $sql ] as $source => $calls ) {
				printf(
					'%s<br><span class="qm-info">&nbsp;%s</span><br>',
					esc_html( $source ),
					esc_html( sprintf(
						_n( '%s call', '%s calls', $calls, 'query-monitor' ),
						number_format_i18n( $calls )
					) )
				);
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table>';
		echo '</div>';

	}

	public function admin_menu( array $menu ) {

		if ( $dbq = QM_Collectors::get( 'db_dupes' ) ) {
			$dbq_data = $dbq->get_data();
			if ( isset( $dbq_data['dupes'] ) && count( $dbq_data['dupes'] ) ) {
				$menu[] = $this->menu( array(
					'title' => esc_html( sprintf(
						__( 'Duplicate Queries (%s)', 'query-monitor' ),
						count( $dbq_data['dupes'] )
					) ),
				) );
			}
		}
		return $menu;

	}

}

function register_qm_output_html_db_dupes( array $output, QM_Collectors $collectors ) {
	if ( $collector = QM_Collectors::get( 'db_dupes' ) ) {
		$output['db_dupes'] = new QM_Output_Html_DB_Dupes( $collector );
	}
	return $output;
}

add_filter( 'qm/outputter/html', 'register_qm_output_html_db_dupes', 45, 2 );
