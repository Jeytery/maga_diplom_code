//
//  AdminCoordinator.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 05.12.2024.
//

import Foundation
import UIKit

final class AdminCoordinator: Coordinatable {
    private(set) var navigationController = UINavigationController()
    
    override func startCoordinator() {
        super.startCoordinator()
        let startVC = UIViewController()
        startVC.view.backgroundColor = .systemBackground
        navigationController.setViewControllers([startVC], animated: false)
        LoaderOverlayProvider.shared.overlay()
        DispatchQueue.main.asyncAfter(deadline: .now() + 1, execute: {
            LoaderOverlayProvider.shared.remove()
            self.showMainMenu()
        })
    }
    
    private func showMainMenu() {
        let scenarioVC = RouteViewControllerScenario(scenario: ScenarioDataProvider.adminScenarioActions())
        scenarioVC.polylinesColor = .systemBlue
        let nvc = UINavigationController(rootViewController: scenarioVC)
        nvc.modalPresentationStyle = .fullScreen
        nvc.modalTransitionStyle = .crossDissolve
        scenarioVC.navigationItem.rightBarButtonItem = .init(image: UIImage(systemName: "gear"), style: .plain, target: nil, action: nil)
        setNavigationBarBlur(nvc)
        navigationController.present(nvc, animated: true)
    }
        
    private func setNavigationBarBlur(_ navigationController: UINavigationController) {
        let appearance = UINavigationBarAppearance()
        appearance.configureWithTransparentBackground()
        appearance.backgroundEffect = UIBlurEffect(style: .light)
        
        navigationController.navigationBar.standardAppearance = appearance
        navigationController.navigationBar.scrollEdgeAppearance = appearance
    }
}
