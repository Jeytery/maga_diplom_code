//
//  LoaderOverlayView.swift
//  role-cards
//
//  Created by Dmytro Ostapchenko on 17.02.2024.
//

import Foundation
import UIKit

class LoaderOverlayView: UIView {
    override init(frame: CGRect) {
        super.init(frame: frame)
        let loader = UIActivityIndicatorView(style: .whiteLarge)
        backgroundColor = .black.withAlphaComponent(0.3)
        addSubview(loader)
        loader.translatesAutoresizingMaskIntoConstraints = false
        loader.centerXAnchor.constraint(equalTo: centerXAnchor).isActive = true
        loader.centerYAnchor.constraint(equalTo: centerYAnchor).isActive = true
        loader.startAnimating()
    }
   
    required init?(coder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
}
